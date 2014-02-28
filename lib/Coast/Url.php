<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Url
{
    const PART_SCHEME   = 0;
    const PART_USERNAME = 1;
    const PART_PASSWORD = 2;
    const PART_HOST     = 3;
    const PART_PORT     = 4;
    const PART_PATH     = 5;
    const PART_QUERY    = 6;
    const PART_FRAGMENT = 7;
    
    const SCHEME_HTTP   = 'http';
    const SCHEME_HTTPS  = 'https';
    const SCHEME_MAILTO = 'mailto';

    protected static $_colons = [
        self::SCHEME_MAILTO    => ':',
    ];
    
    protected $_scheme;
    protected $_username;
    protected $_password;
    protected $_host;
    protected $_port;
    protected $_path;
    protected $_queryParams = [];
    protected $_fragment;
        
    public function __construct($string = null)
    {
        if (!isset($string)) {
            return;
        }
        
        $data = array_merge(array(
            'scheme'   => null,
            'user'     => null,
            'pass'     => null,
            'host'     => null,
            'port'     => null,
            'path'     => null,
            'query'    => null,
            'fragment' => null,
        ), parse_url($string));
        $this->scheme($data['scheme']);
        $this->username($data['user']);
        $this->password($data['pass']);
        $this->host($data['host']);
        $this->port($data['port']);
        $this->path($data['path']);
        $this->query($data['query']);
        $this->fragment($data['fragment']);
    }
    
    public function toString($to = null, $start = false)
    {
        $parts = array_fill(self::PART_SCHEME, self::PART_FRAGMENT + 1, null);
        
        if (isset($this->_scheme)) {
            $parts[self::PART_SCHEME]           = $this->scheme();
            $parts[self::PART_SCHEME]          .= isset(self::$_colons[$this->_scheme]) ? self::$_colons[$this->_scheme] : '://';
        } else if (isset($this->_host)) {
            $parts[self::PART_SCHEME]           = '//';
        }
        if (isset($this->_username)) {
            $parts[self::PART_USERNAME]         = $this->username();
            if (isset($this->_password)) {
                $parts[self::PART_PASSWORD]     = ':' . $this->password() . '@';
            } else {    
                $parts[self::PART_USERNAME]    .= '@';
            }
        }
        if (isset($this->_host)) {
            $parts[self::PART_HOST]             = $this->host();
            if (isset($this->_port)) {
                $parts[self::PART_PORT]         = ':' . $this->port();
            }
        }
        if (isset($this->_path)) {
            $parts[self::PART_PATH]             = $this->path();
        }
        if (count($this->_queryParams) > 0) {
            $parts[self::PART_QUERY]            = '?' . $this->query();
        }
        if (isset($this->_fragment)) {
            $parts[self::PART_FRAGMENT]         = '#' . $this->fragment();
        }
        
        if (!isset($to)) {
            $to = $start
                ? self::PART_FRAGMENT
                : self::PART_SCHEME;
        }
        
        if ($start) {
            $parts = array_slice($parts, self::PART_SCHEME, $to + 1);
        } else {
            $parts = array_slice($parts, $to);            
        }
        
        return implode(null, $parts);
    }

    public function __toString()
    {
        return $this->toString();
    }
    
    public function scheme($value = null)
    {
        if (isset($value)) {
            $this->_scheme = $value;
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
    
    public function username($value = null)
    {
        if (isset($value)) {
            $this->_username = $value;
            return $this;
        }
        return $this->_username;
    }

    public function password($value = null)
    {
        if (isset($value)) {
            $this->_password = $value;
            return $this;
        }
        return $this->_password;
    }
    
    public function host($value = null)
    {
        if (isset($value)) {
            $this->_host = $value;
            return $this;
        }
        return $this->_host;
    }

    public function port($value = null)
    {
        if (isset($value)) {
            $this->_port = $value;
            return $this;
        }
        return $this->_port;
    }

    public function path($value = null)
    {
        if (isset($value)) {
            $this->_path = $value;
            return $this;
        }
        return $this->_path;
    }

    public function queryParam($name, $value = null)
    {
        if (isset($value)) {
            $this->_queryParams[$name] = $value;
            return $this;
        }
        return isset($this->_queryParams[$name])
            ? $this->_queryParams[$name]
            : null;
    }

    public function queryParams(array $querys = null)
    {
        if (isset($querys)) {
            foreach ($querys as $name => $value) {
                $this->queryParam($name, $value);
            }
            return $this;
        }
        return $this->_queryParams;
    }

    public function query($value = null)
    {
        if (isset($value)) {
            parse_str($value, $params);
            $this->queryParams($params);
            return $this;
        }
        $query = array();
        foreach ($this->queryParams() as $name => $value) {
            $query[] = $name . (strlen($value) ? '=' . urlencode($value) : null);
        }
        return implode('&', $query);
    }

    public function fragment($value = null)
    {
        if (isset($value)) {
            $this->_fragment = $value;
            return $this;
        }
        return $this->_fragment;
    }
}