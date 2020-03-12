<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Url;
use Coast\File;
use Coast\Http;

class Http
{
    protected $_timeout;
    protected $_cookies;
    
    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new \Coast\Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
    }

    public function timeout($timeout = null)
    {
        if (func_num_args() > 0) {
            $this->_timeout = $timeout;
            return $this;
        }
        return $this->_timeout;
    }
    
    public function execute(Http\Request $request)
    {
        $method  = $request->method();
        $url     = $request->url();
        $body    = $request->body();
        $headers = $request->headers();
        $auth    = $request->auth();

        if (!isset($method)) {
            throw new Http\Exception("No method set");
        }
        if (!isset($url)) {
            throw new Http\Exception("No URL set");
        }

        $ch = curl_init((string) $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        if (!ini_get('open_basedir')) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (isset($this->_timeout)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        }
        if (isset($this->_cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->_cookies);
        }
        if (isset($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function($value, $name) {
                return "{$name}: {$value}";
            }, $headers, array_keys($headers)));
        }
        if (isset($auth)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, $auth['type']);
            curl_setopt($ch, CURLOPT_USERPWD, "{$auth['username']}:{$auth['password']}");
        }
        if ($method == Http\Request::METHOD_HEAD) {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        } else if ($method == Http\Request::METHOD_POST) {
            if (is_array($body)) {
                foreach ($body as $name => $value) {
                    if ($value instanceof File) {
                        $value = [$value];
                    }
                    if (is_array($value) && $value[0] instanceof File) {
                        $value = $value + [null, null, null];
                        $value = new \CURLFile($value[0], $value[1], $value[2]);
                    }
                    $body[$name] = $value;
                }
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        
        $response = curl_exec($ch);
        if ($response === false) {
            throw new Http\Exception(curl_error($ch));
        }
        
        $url    = new Url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $size   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $head   = substr($response, 0, $size);
        $body   = $method != Http\Request::METHOD_HEAD
            ? substr($response, $size)
            : null;
        
        $headers = [];
        if ($head) {
            $parts   = explode("\r\n\r\n", $head);
            $head    = $parts[count($parts) - 2];
            $head    = explode("\r\n", $head);
            $headers = [];
            foreach ($head as $header) {
                $parts = explode(':', $header, 2);
                if (count($parts) != 2) {
                    continue;
                }
                $name  = trim($parts[0]);
                $value = trim($parts[1]);
                $headers[$name] = $value;
            }
        }
        
        if (isset($headers['cookie'])) {
            $this->_cookies = $headers['cookie'];
        }
        
        curl_close($ch);
        return new Http\Response([
            'request' => $request,
            'url'     => $url,
            'status'  => $status,
            'headers' => $headers,
            'body'    => $body,
        ]);
    }
}