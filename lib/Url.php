<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Http;
use DOMDocument;
use DOMXPath;

class Url
{
    const PART_SCHEME   = 0;
    const PART_USER     = 1;
    const PART_PASS     = 2;
    const PART_HOST     = 3;
    const PART_PORT     = 4;
    const PART_PATH     = 5;
    const PART_QUERY    = 6;
    const PART_FRAGMENT = 7;
        
    protected $_scheme;
    protected $_user;
    protected $_pass;
    protected $_host;
    protected $_port;
    protected $_path;
    protected $_queryParams = [];
    protected $_fragment;
        
    public function __construct($value = null)
    {
        if (isset($value)) {
            if (is_array($value)) {
                $this->fromArray($value);
            } else {
                $this->fromString($value);
            }
        }
    }

    public function fromString($value)
    {
        $parts = parse_url($value);
        $this->fromArray($parts);
        return $this;
    }

    public function toString()
    {
        // Optimisation to skip complex URL build when all we have is a path
        if (!isset($this->_scheme) &&
            !isset($this->_user) &&
            !isset($this->_pass) &&
            !isset($this->_host) &&
            !isset($this->_port) &&
            !count($this->_queryParams) &&
            !isset($this->_fragment) &&
            isset($this->_path)) {
            return $this->_path->name();
        }

        $string = http_build_url($this->toArray());
        $string = preg_replace('/^(mailto|tel|data):\/{3}/', '$1:', $string);
        if (!isset($this->_scheme) &&
            !isset($this->_user) &&
            !isset($this->_pass) &&
            !isset($this->_host) &&
            !isset($this->_port) &&
            (!isset($this->_path) || substr($this->_path->name(), 0, 1) != '/')) {
            $string = ltrim($string, '/');
        } else if (!isset($this->_path) &&
            !isset($this->_fragment) &&
            !count($this->_queryParams)) {
            $string = rtrim($string, '/');
        }
        return $string;
    }

    public function fromArray(array $parts)
    {
        $parts = array_intersect_key($parts, [
            'scheme',
            'user',
            'pass',
            'host',
            'port',
            'path',
            'query',
            'queryParams',
            'fragment',
        ]);
        foreach ($parts as $method => $value) {
            $this->{$method}($value);
        }
        return $this;
    }

    public function toArray()
    {
        return [
            'scheme'   => $this->scheme(),
            'user'     => $this->user(),
            'pass'     => $this->pass(),
            'host'     => $this->host(),
            'port'     => $this->port(),
            'path'     => $this->path(),
            'query'    => $this->query(),
            'fragment' => $this->fragment(),
        ];
    }

    public function toPart($part, $reverse = false)
    {
        return new Url($reverse
            ? array_slice($this->toArray(), $part)
            : array_slice($this->toArray(), 0, $part + 1));
    }
    
    public function __toString()
    {
        return $this->toString();
    }
    
    public function scheme($scheme = null)
    {
        if (func_num_args() > 0) {
            $this->_scheme = $scheme;
            return $this;
        }
        return $this->_scheme;
    }

    public function isHttp()
    {
        $scheme = strtolower($this->scheme());
        return $scheme == 'http' || $scheme == 'https';
    }
    
    public function isHttps()
    {
        $scheme = strtolower($this->scheme());
        return $scheme == 'https';
    }
    
    public function user($user = null)
    {
        if (func_num_args() > 0) {
            $this->_user = $user;
            return $this;
        }
        return $this->_user;
    }

    public function pass($pass = null)
    {
        if (func_num_args() > 0) {
            $this->_pass = $pass;
            return $this;
        }
        return $this->_pass;
    }
    
    public function host($host = null)
    {
        if (func_num_args() > 0) {
            $this->_host = $host;
            return $this;
        }
        return $this->_host;
    }

    public function port($port = null)
    {
        if (func_num_args() > 0) {
            $this->_port = $port;
            return $this;
        }
        return $this->_port;
    }

    public function path($path = null)
    {
        if (func_num_args() > 0) {
            $this->_path = !$path instanceof \Coast\Path
                ? new \Coast\Path("{$path}")
                : $path;
            return $this;
        }
        return $this->_path;
    }

    public function queryParam($name, $value = null)
    {
        if (func_num_args() > 1) {
            $this->_queryParams[$name] = $value;
            return $this;
        }
        return isset($this->_queryParams[$name])
            ? $this->_queryParams[$name]
            : null;
    }

    public function queryParams(array $querys = null)
    {
        if (func_num_args() > 0) {
            foreach ($querys as $name => $value) {
                $this->queryParam($name, $value);
            }
            return $this;
        }
        return $this->_queryParams;
    }

    public function query($query = null)
    {
        if (func_num_args() > 0) {
            parse_str($query, $params);
            $this->queryParams($params);
            return $this;
        }
        return count($this->_queryParams)
            ? http_build_query($this->queryParams(), '', '&', PHP_QUERY_RFC3986)
            : null;
    }

    public function fragment($fragment = null)
    {
        if (func_num_args() > 0) {
            $this->_fragment = $fragment;
            return $this;
        }
        return $this->_fragment;
    }

    /**
     * Is URL absolute.
     * @return bool
     */
    public function isAbsolute()
    {
        return isset($this->_scheme);
    }

    /**
     * Is URL relative.
     * @return bool
     */
    public function isRelative()
    {
        return !$this->isAbsolute();
    }

    public function toAbsolute(Url $base)
    {
        if (!$this->isRelative() || !$base->isAbsolute()) {
            throw new \Exception("URL '{$this}' is not relative or base URL '{$base}' is not absolute");
        }

        $current = $this->toArray();
        $base    = $base->toArray();
        $switch  = false;
        $temp    = [];
        foreach ($base as $name => $value) {
            if (!$switch && isset($current[$name])) {
                $switch = true;
            }
            if ($switch) {
                $value = $name == 'path' && $current[$name]->isRelative()
                    ? $current[$name]->toAbsolute($base[$name])
                    : $current[$name];
            }
            $temp[$name] = $value;
        }
        $url = new Url($temp);
        return $url;
    }

    public function toCanonical()
    {
        $url = clone $this;
        if (!$url->isHttp()) {
            return $url;
        }
        $http = new Http([
            'timeout' => 5,
        ]);
        $req = new Http\Request([
            'url' => $url,
        ]);
        $res = $http->execute($req);
        if (!$res->isSuccess()) {
            return $url;
        }
        $url = $res->url();
        if (!preg_match('/^text\/html/i', $res->header('content-type'))) {
            return $url;
        }
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $result = $doc->loadHTML($res->body());
        libxml_use_internal_errors(false);
        if (!$result) {
            return $url;
        }
        $types = [
            ['//link[@rel="canonical"]',   'href'],
            ['//meta[@property="og:url"]', 'content'],
        ];
        $xpath = new DOMXPath($doc);
        foreach ($types as $type) {
            $els = $xpath->query($type[0]);
            if ($els->length) {
                $temp = new Url($els->item(0)->getAttribute($type[1]));
                if ($temp->isRelative()) {
                    $temp = $temp->toAbsolute($url);
                }
                $url = $temp;
                break;
            }
        }    
        return $url;
    }
}