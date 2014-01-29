<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Http;

class Response
{
    protected $_status;
    protected $_headers = [];
    protected $_body;

    public function __construct($status, array $headers, $body)
    {
        $this->_status = $status;
        $this->_body   = $body;
        foreach ($headers as $name => $value) {
            $this->_headers[strtolower($name)] = $value;
        }
    }

    public function status()
    {
        return $this->_status;
    }

    public function headers()
    {
        return $this->_headers;
    }

    public function header($name)
    {
        return isset($this->_headers[strtolower($name)])
            ? $this->_headers[strtolower($name)]
            : null;
    }

    public function body()
    {
        return $this->_body;
    }

    public function information()
    {
        return $this->_status >= 100 && $this->_status <= 199;
    }

    public function success()
    {
        return $this->_status >= 200 && $this->_status <= 299;
    }

    public function redirect()
    {
        return $this->_status >= 300 && $this->_status <= 399;
    }

    public function clientError()
    {
        return $this->_status >= 400 && $this->_status <= 499;
    }

    public function serverError()
    {
        return $this->_status >= 500 && $this->_status <= 599;
    }

    public function error()
    {
        return $this->clientError() || $this->serverError();
    }
}