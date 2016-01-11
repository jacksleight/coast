<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Http;

use Coast\Url;
use Coast\Http\Request;

class Response
{
    protected $_request;
    protected $_url;
    protected $_status;
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

    public function request(Request $request = null)
    {
        if (func_num_args() > 0) {
            $this->_request = $request;
            return $this;
        }
        return $this->_request;
    }

    public function url(Url $url = null)
    {
        if (func_num_args() > 0) {
            if (!$url->isHttp()) {
                throw new \Exception("URL scheme is not HTTP or HTTPS");
            }
            $this->_url = $url;
            return $this;
        }
        return $this->_url;
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

    public function type()
    {
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

    public function json($assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($this->_body, $assoc, $depth, $options);
    }

    public function xml($class = '\Coast\Xml', $options = 0, $namespace = '', $prefix = false)
    {
        return new $class($this->_body, $options, false, $namespace, $prefix);
    }

    public function isJson()
    {
        return (bool) preg_match('/^application\/json$/i', $this->type());
    }

    public function isXml()
    {
        return (bool) preg_match('/^application\/([-\w]+\+)?xml$/i', $this->type());
    }

    public function isInformation()
    {
        return $this->_status >= 100 && $this->_status <= 199;
    }

    public function isSuccess()
    {
        return $this->_status >= 200 && $this->_status <= 299;
    }

    public function isRedirect()
    {
        return $this->_status >= 300 && $this->_status <= 399;
    }

    public function isClientError()
    {
        return $this->_status >= 400 && $this->_status <= 499;
    }

    public function isServerError()
    {
        return $this->_status >= 500 && $this->_status <= 599;
    }

    public function isError()
    {
        return $this->isClientError() || $this->isServerError();
    }
}