<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
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

    public function header($name)
    {
        return isset($this->_headers[strtolower($name)])
            ? $this->_headers[strtolower($name)]
            : null;
    }

    public function headers()
    {
        return $this->_headers;
    }

    public function body()
    {
        return $this->_body;
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