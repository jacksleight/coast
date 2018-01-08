<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\File;
use Coast\Request;

class Response
{
    protected $_req;

    protected $_status  = 200;
    protected $_headers = [];
    protected $_cookies = [];
    protected $_body    = null;
    protected $_params  = [];

    public function __construct(Request $request = null)
    {
        $this->request($request);
    }

    public function request(Request $request = null)
    {
        if (func_num_args() > 0) {
            $this->_request = $request;
            return $this;
        }
        return $this->_request;
    }

    public function toGlobals()
    {
        http_response_code($this->_status);
        foreach ($this->_headers as $name => $value) {
            header("{$name}: {$value}");
        }
        foreach ($this->_cookies as $name => $params) {
            call_user_func_array('setcookie', $params);
        }
        if ($this->_body instanceof File) {
            $this->_body->output();
            if ($this->_body->isOpen()) {
                $this->_body->close();
            }
        } else {
            echo $this->_body;
        }
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
        if (func_num_args() > 0) {
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

    public function cookie($name, $value = null, $age = null, $path = null, $domain = null, $secure = false, $http = false)
    {
        if (func_num_args() > 0) {
            if (!isset($path) && isset($this->_request)) {
                $path = $this->_request->base();
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

    public function file($data, $type, $attachment = false, $name = null)
    {
        if ($data instanceof File) {
            if ($name === true) {
                $name = $data->baseName();
            }
            $length = $data->size();
        } else {
            $data = (string) $data;
            $length = mb_strlen($data, 'UTF-8');
        }
        $this->_body = $data;
        $this
            ->type($type)
            ->header('Cache-Control', "public")
            ->header('Content-Length', $length);
        if ($attachment) {
            $this->attachment($name);
        }
        return $this;
    }

    public function attachment($name = null)
    {
        $this->header('Content-Disposition', "attachment" . (isset($name) ? "; filename={$name}" : null));
        return $this;
    }

    public function redirect($url, $type = 301)
    {
        return $this
            ->status($type)
            ->header('Location', $url);
    }

    public function param($name, $value = null)
    {
        if (func_num_args() > 1) {
            if (isset($value)) {
                $this->_params[$name] = $value;
            } else {
                unset($this->_params[$name]);
            }
            return $this;
        }
        return isset($this->_params[$name])
            ? $this->_params[$name]
            : null;
    }

    public function params(array $params = null)
    {
        if (func_num_args() > 0) {
            foreach ($params as $name => $value) {
                $this->param($name, $value);
            }
            return $this;
        }
        return $this->_params;
    }

    /**
     * Set a parameter.
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function __set($name, $value)
    {
        return $this->param($name, $value);
    }

    /**
     * Get a parameter.
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->param($name);
    }

    /**
     * Check if a parameter exists.
     * @param  string  $name
     * @return boolean
     */
    public function __isset($name)
    {
        return $this->param($name) !== null;
    }

    /**
     * Unset a parameter.
     * @param  string  $name
     * @return boolean
     */
    public function __unset($name)
    {
        return $this->param($name, null);
    }
}