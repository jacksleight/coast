<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Response
{
    protected $_req;
    
    protected $_status  = 200;
    protected $_headers = [];
    protected $_cookies = [];
    protected $_body    = '';

    public function __construct(\Coast\Request $req)
    {
        $this->_req = $req;
    }

    public function request()
    {
        return $this->_req;
    }

    public function toGlobals()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            $_SESSION = $this->_req->sessions();
            session_write_close();
        }
        http_response_code($this->_status);
        foreach ($this->_headers as $name => $value) {
            header("{$name}: {$value}");
        }
        foreach ($this->_cookies as $name => $params) {
            call_user_func_array('setcookie', $params);
        }
        echo $this->body();
    }

    public function status($status = null)
    {
        if (func_num_args() > 0) {
            $this->_status = $status;
            return $this;
        }
        return $this->_status;
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
        return $this->header('Content-Type');
    }

    public function cookie($name, $value = null, $age = null, $path = null, $domain = null, $secure = false, $http = false)
    {
        if (func_num_args() > 0) {
            if (!isset($path)) {
                $path = $this->_req->base();
            }
            $this->_cookies[$name] = [$name, $value, (isset($age) ? time() + $age : null), $path, $domain, $secure, $http];
            return $this;
        }
        return isset($this->_cookies[$name])
            ? $this->_cookies[$name]
            : null;
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

    public function redirect($url, $type = 301)
    {
        return $this
            ->status($type)
            ->header('Location', $url);
    }
}