<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class Response
{
    protected $_req;
    
    protected $_status  = null;
    protected $_headers = [];
    protected $_cookies = [];
    protected $_body    = '';

    public function __construct(\Coast\App\Request $req)
    {
        $this->_req = $req;
        $this->status(200);
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
        header($this->_req->protocol() . $this->_status);
        foreach ($this->_headers as $name => $value) {
            header("{$name}: {$value}");
        }
        foreach ($this->_cookies as $name => $params) {
            call_user_func_array('setcookie', $params);
        }
        echo $this->body();
    }

    public function status($value = null)
    {
        if (isset($value)) {
            $this->_status = $value;
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
        if (isset($headers)) {
            foreach ($headers as $name => $value) {
                $this->header($name, $value);
            }
            return $this;
        }
        return $this->_headers;
    }

    public function type($value = null)
    {
        if (isset($value)) {
            $this->header('Content-Type', $value);
            return $this;
        }
        return $this->header('Content-Type');
    }

    public function cookie($name, $value = null, $age = null, $path = null, $domain = null, $secure = false, $http = false)
    {
        if (isset($value)) {
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

    public function body($data = null)
    {
        if (isset($data)) {
            $this->_body = $data;
            return $this;
        }
        return $this->_body;
    }

    public function text($data)
    {
        return $this
            ->type('text/plain')
            ->body((string) $data);
    }

    public function html($data)
    {
        return $this
            ->type('text/html')
            ->body((string) $data);
    }

    public function json($data, $options = JSON_PRETTY_PRINT, $depth = 512)
    {
        return $this
            ->type('application/json')
            ->body(json_encode($data, $options, $depth));
    }

    public function xml($data, $type = null, $options = null)
    {  
        if ($data instanceof \SimpleXMLElement) {
            $data = $data->asXML();
        } else if ($data instanceof \DOMDocument) {
            $data = $data->saveXML($options);
        }
        return $this
            ->type(isset($type)
                ? "application/{$type}+xml"
                : 'application/xml')
            ->body((string) $data);
    }

    public function redirect($url, $type = 301)
    {
        return $this
            ->status($type)
            ->header('Location', $url);
    }
}