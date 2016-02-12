<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Http;

use Coast\Url;
use Coast\Http\Response;
use Coast\File;

class Request
{
    const METHOD_HEAD   = 'HEAD';
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';

    const AUTH_ANY          = CURLAUTH_ANY;
    const AUTH_ANYSAFE      = CURLAUTH_ANYSAFE;
    const AUTH_BASIC        = CURLAUTH_BASIC;
    const AUTH_DIGEST       = CURLAUTH_DIGEST;
    const AUTH_GSSNEGOTIATE = CURLAUTH_GSSNEGOTIATE;
    const AUTH_NTLM         = CURLAUTH_NTLM;

    protected $_method = self::METHOD_GET;
    protected $_url;
    protected $_auth;
    protected $_headers = [];
    protected $_body;

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new \Coast\Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
    }

    public function method($method = null)
    {
        if (func_num_args() > 0) {
            $this->_method = $method;
            return $this;
        }
        return $this->_method;
    }

    public function url(Url $url = null)
    {
        if (func_num_args() > 0) {
            if (!$url->isHttp() || !$url->isAbsolute()) {
                throw new \Exception("URL must be HTTP and absolute");
            }
            $this->_url = $url;
            return $this;
        }
        return $this->_url;
    }

    public function auth(array $auth = null)
    {
        if (func_num_args() > 0) {
            $auth = $auth + [
                'type'     => self::AUTH_ANY,
                'username' => null,
                'password' => null,
            ];
            $this->_auth = $auth;
            return $this;
        }
        return $this->_auth;
    }

    public function header($name, $value = null)
    {
        $name = strtolower($name);
        if (isset($value)) {
            $this->_headers[$name] = $value;
            return $this;
        }
        return isset($this->_headers[$name])
            ? $this->_headers[$name]
            : null;
    }

    public function headers(array $headers = null)
    {
        if (func_num_args() > 0) {
            foreach ($headers as $name => $value) {
                $this->header($name, $value);
            }
            return $this;
        }
        return $this->_headers;
    }

    public function type($type = null)
    {
        if (func_num_args() > 0) {
            $this->header('Content-Type', $type);
            return $this;
        }
        return current(explode(';', $this->header('Content-Type')));
    }

    public function body($body = null)
    {
        if (func_num_args() > 0) {
            $this->_body = $body;
            return $this;
        }
        return $this->_body;
    }

    public function text($text)
    {
        return $this
            ->type('text/plain')
            ->body((string) $text);
    }

    public function html($html)
    {
        return $this
            ->type('text/html')
            ->body((string) $html);
    }

    public function json($json, $options = JSON_PRETTY_PRINT, $depth = 512)
    {
        return $this
            ->type('application/json')
            ->body(json_encode($json, $options, $depth));
    }

    public function xml($xml, $type = null, $options = null)
    {  
        if ($xml instanceof \SimpleXMLElement) {
            $xml = $xml->asXML();
        } else if ($xml instanceof \DOMDocument) {
            $xml = $xml->saveXML($options);
        }
        return $this
            ->type(isset($type)
                ? "application/{$type}+xml"
                : 'application/xml')
            ->body((string) $xml);
    }

    public function file(File $file, $type = null)
    {  
        $this
            ->body([
                [$file, $type],
            ]);
        return $this;
    }
}