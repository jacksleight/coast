<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class Request
{
    const PROTOCOL_10   = 'HTTP/1.0';
    const PROTOCOL_11   = 'HTTP/1.1';
    
    const METHOD_HEAD   = 'HEAD';
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';
    
    const SCHEME_HTTP   = 'http';
    const SCHEME_HTTPS  = 'https';
    
    const PORT_HTTP     = 80;
    const PORT_HTTPS    = 443;

    protected $_response;
    
    protected $_sessions    = [];
    protected $_params      = [];
    protected $_servers     = [];
    protected $_protocol;
    protected $_method;
    protected $_headers     = [];
    protected $_scheme;
    protected $_host;
    protected $_port;
    protected $_base;
    protected $_path;
    protected $_queryParams = [];
    protected $_bodyParams  = [];
    protected $_body        = [];
    protected $_cookies     = [];

    public function __construct()
    {
        $this->_response = new \Coast\App\Response($this);
    }

    public function response()
    {
        return $this->_response;
    }

    public function import()
    {
        $this->params(isset($_SERVER['argv']) ? $_SERVER['argv'] : []);
        
        $this->servers($_SERVER);

        $this->protocol(strtoupper($this->server('SERVER_PROTOCOL')));
        $this->method(strtoupper($this->server('REQUEST_METHOD')));

        foreach ($this->servers() as $name => $value) {
            if (preg_match('/^HTTP_(.*)$/', $name, $match)) {
                $this->header(str_replace('_', '-', $match[1]), $value);
            }
        }
        if (function_exists('apache_request_headers')) {
            foreach (apache_request_headers() as $name => $value) {
                $this->header($name, $value);
            }
        }

        $this->scheme($this->server('HTTPS') == 'on' ? self::SCHEME_HTTPS : self::SCHEME_HTTP);
        $this->host($this->server('SERVER_NAME'));
        $this->port($this->server('SERVER_PORT'));
    
        list($full) = explode('?', $this->server('REQUEST_URI'));    
        $path = isset($_GET['_']) ? $_GET['_'] : ltrim($full, '/');
        $full = explode('/', $full);
        $path = explode('/', $path);
        $base = array_slice($full, 0, count($full) - count($path));
        $this->base(implode('/', $base) . '/');
        $this->path(implode('/', $path));

        $this->queryParams($this->_clean($_GET));
        $this->bodyParams($this->_clean(array_merge($_POST, $_FILES)));
        $this->body(file_get_contents('php://input'));
        $this->cookies($_COOKIE);

        if (session_status() == PHP_SESSION_NONE) {
            session_set_cookie_params(0, $this->base());
            session_start();
        }
        $this->sessions($_SESSION);

        return $this;
    }

    protected function _clean(array $params)
    {
        foreach ($params as $name => $value) {
            if (preg_match('/^_/', $name)) {
                unset($params[$name]);
                continue;
            }
            $value = trim($value);
            if (strlen($value) == 0) {
                $value = null;
            }
            $params[$name] = $value;
        }
        return $params;
    }

    public function &session($name, $value = null)
    {
        if (isset($value)) {
            $this->_sessions[$name] = $value;
            return $this;
        }
        return isset($this->_sessions[$name])
            ? $this->_sessions[$name]
            : null;
    }

    public function &sessions(array $sessions = null)
    {
        if (isset($sessions)) {
            foreach ($sessions as $name => $value) {
                $this->session($name, $value);
            }
            return $this;
        }
        return $this->_sessions;
    }

    public function param($name, $value = null)
    {
        if (isset($value)) {
            $this->_params[$name] = $value;
            return $this;
        }
        return isset($this->_params[$name])
            ? $this->_params[$name]
            : null;
    }

    public function params(array $params = null)
    {
        if (isset($params)) {
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
    public function set($name, $value)
    {
        return $this->param($name, $value);
    }

    /**
     * Get a parameter.
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->param($name);
    }

    /**
     * Check if a parameter exists.
     * @param  string  $name
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->_params[$name]);
    }

    public function server($name, $value = null)
    {
        if (isset($value)) {
            $this->_servers[$name] = $value;
            return $this;
        }
        return isset($this->_servers[$name])
            ? $this->_servers[$name]
            : null;
    }

    public function servers(array $servers = null)
    {
        if (isset($servers)) {
            foreach ($servers as $name => $value) {
                $this->server($name, $value);
            }
            return $this;
        }
        return $this->_servers;
    }

    public function protocol($value = null)
    {
        if (isset($value)) {
            $this->_protocol = $value;
            return $this;
        }
        return $this->_protocol;
    }

    public function method($value = null)
    {
        if (isset($value)) {
            $this->_method = $value;
            return $this;
        }
        return $this->_method;
    }

    public function isHead()
    {
        return $this->method() == self::METHOD_HEAD;
    }

    public function isGet()
    {
        return $this->method() == self::METHOD_GET;
    }

    public function isPost()
    {
        return $this->method() == self::METHOD_POST;
    }

    public function isPut()
    {
        return $this->method() == self::METHOD_PUT;
    }

    public function isDelete()
    {
        return $this->method() == self::METHOD_DELETE;
    }

    public function isAjax()
    {
        return $this->header('X-Requested-With') == 'XMLHttpRequest';
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

    public function scheme($value = null)
    {
        if (isset($value)) {
            $this->_scheme = $value;
            return $this;
        }
        return $this->_scheme;
    }

    public function isSecure()
    {
        return $this->scheme() == self::SCHEME_HTTPS;
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

    public function base($value = null)
    {
        if (isset($value)) {
            $this->_base = $value;
            return $this;
        }
        return $this->_base;
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
            $this->param($name, $value);
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

    public function bodyParam($name, $value = null)
    {
        if (isset($value)) {
            $this->_bodyParams[$name] = $value;
            $this->param($name, $value);
            return $this;
        }
        return isset($this->_bodyParams[$name])
            ? $this->_bodyParams[$name]
            : null;
    }

    public function bodyParams(array $datas = null)
    {
        if (isset($datas)) {
            foreach ($datas as $name => $value) {
                $this->bodyParam($name, $value);
            }
            return $this;
        }
        return $this->_bodyParams;
    }

    public function body($value = null)
    {
        if (isset($value)) {
            $this->_body = $value;
            return $this;
        }
        return $this->_body;
    }

    public function json($assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($this->_body, $assoc, $depth, $options);
    }

    public function xml($class = 'SimpleXMLElement', $options = 0, $namespace = '', $prefix = false)
    {
        return new $class($this->_body, $options, false, $namespace, $prefix);
    }

    public function cookie($name, $value = null)
    {
        if (isset($value)) {
            $this->_cookies[$name] = $value;
            return $this;
        }
        return isset($this->_cookies[$name])
            ? $this->_cookies[$name]
            : null;
    }

    public function cookies(array $cookies = null)
    {
        if (isset($cookies)) {
            foreach ($cookies as $name => $value) {
                $this->cookie($name, $value);
            }
            return $this;
        }
        return $this->_cookies;
    }

    public function url()
    {
        $default = 
            ($this->scheme() == self::SCHEME_HTTP  && $this->port() == self::PORT_HTTP) ||
            ($this->scheme() == self::SCHEME_HTTPS && $this->port() == self::PORT_HTTPS);
        $url = new \Coast\Url();
        $url->scheme($this->scheme());
        $url->host($this->host());
        $url->port(!$default ? $this->port() : null);
        $url->path($this->base() . $this->path());
        $url->queryParams($this->queryParams());
        return $url;
    }

    /**
     * Alias of `set`
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Alias of `get`
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }
    
    /**
     * Alias of `has`
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }
}