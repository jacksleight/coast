<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

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
        if (!$parts) {
            return;
        }
        $this->fromArray($parts);
        return $this;
    }

    public function toString()
    {
        $string = http_build_url($this->toArray());
        $string = preg_replace('/^(mailto|tel):\/{3}/', '$1:', $string);
        if (!isset($this->_scheme) &&
            !isset($this->_user) &&
            !isset($this->_pass) &&
            !isset($this->_host) &&
            !isset($this->_port) &&
            (!isset($this->_path) || $this->_path->name()[0] != '/')) {
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
    
    public function parts($parts = null)
    {
        if (func_num_args() > 0) {
            $parts = array_merge([
                'scheme'   => null,
                'user'     => null,
                'pass'     => null,
                'host'     => null,
                'port'     => null,
                'path'     => null,
                'query'    => null,
                'fragment' => null,
            ], $parts);
            $this->scheme($parts['scheme']);
            $this->user($parts['user']);
            $this->pass($parts['pass']);
            $this->host($parts['host']);
            $this->port($parts['port']);
            $this->path($parts['path']);
            $this->query($parts['query']);
            $this->fragment($parts['fragment']);
            return $this;
        }
        return $this->_scheme;
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
        return $scheme == self::SCHEME_HTTP || $scheme == self::SCHEME_HTTPS;
    }
    
    public function isHttps()
    {
        $scheme = strtolower($this->scheme());
        return $scheme == self::SCHEME_HTTPS;
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
            ? http_build_query($this->queryParams())
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
}