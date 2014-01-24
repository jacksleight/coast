<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\App;

class Request
{
    const PROTOCOL_10	= 'HTTP/1.0';
    const PROTOCOL_11	= 'HTTP/1.1';

	const METHOD_HEAD	= 'HEAD';
	const METHOD_GET	= 'GET';
	const METHOD_POST	= 'POST';
	const METHOD_PUT	= 'PUT';
	const METHOD_DELETE	= 'DELETE';

	const SCHEME_HTTP	= 'http';
	const SCHEME_HTTPS	= 'https';

	const PORT_HTTP		= 80;
	const PORT_HTTPS	= 443;

	protected $_response;
	
	protected $_sessions = array();
	protected $_params = array();
	protected $_servers	= array();
	protected $_protocol;
	protected $_method;
	protected $_headers	= array();
	protected $_scheme;
	protected $_host;
	protected $_port;
	protected $_base;
	protected $_path;
	protected $_rawQueryParams	= array();
	protected $_rawPostParams	= array();
	protected $_queryParams		= array();
	protected $_postParams		= array();
	protected $_cookies			= array();

	public function __construct()
	{
		$this->_response = new \Js\App\Response($this);
	}

	public function getResponse()
	{
		return $this->_response;
	}

	public function import()
	{
		$this->addParams(isset($_SERVER['argv']) ? $_SERVER['argv'] : array());
		
		$this->addServers($_SERVER);

		$this->setProtocol(strtoupper($this->getServer('SERVER_PROTOCOL')));
		$this->setMethod(strtoupper($this->getServer('REQUEST_METHOD')));

		foreach ($this->getServers() as $name => $value) {
			if (preg_match('/^HTTP_(.*)$/', $name, $match)) {
				$this->setHeader(str_replace('_', '-', $match[1]), $value);
			}
		}
		if (function_exists('apache_request_headers')) {
			foreach (apache_request_headers() as $name => $value) {
				$this->setHeader($name, $value);
			}
		}

		$this->setScheme($this->getServer('HTTPS') == 'on' ? self::SCHEME_HTTPS : self::SCHEME_HTTP);
		$this->setHost($this->getServer('SERVER_NAME'));
		$this->setPort($this->getServer('SERVER_PORT'));
	
		list($full)	= explode('?', $this->getServer('REQUEST_URI'));	
		$path = isset($_GET['_']) ? $_GET['_'] : ltrim($full, '/');
		$full = explode('/', $full);
		$path = explode('/', $path);
		$base = array_slice($full, 0, count($full) - count($path));
		$this->setBase(implode('/', $base) . '/');
		$this->setPath(implode('/', $path));

		$this->setRawQueryParams($_GET);
		$this->setRawPostParams(array_merge($_POST, $_FILES));

		$this->addQueryParams(\Js\array_expand($this->_cleanParams($_GET), '_'));
		$this->addPostParams(\Js\array_expand($this->_cleanParams(array_merge($_POST, $_FILES)), '_'));
		$this->addCookies($_COOKIE);		

		if (session_status() == PHP_SESSION_NONE) {
			if ($this->hasQueryParam('sessionId')) {
				session_id($this->getQueryParam('sessionId'));
			}
			session_name('sessionId');
			session_set_cookie_params(0, $this->getBase());
			session_start();
		}
		$this->addSessions($_SESSION);

		return $this;
	}

	protected function _cleanParams(array $params)
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

	public function addSessions(array $sessions)
	{
		foreach ($sessions as $name => $value) {
			$this->setSession($name, $value);
		}
		return $this;
	}

	public function setSession($name, $value)
	{
		$this->_sessions[$name] = $value;
		return $this;
	}

	public function &getSessions()
	{
		return $this->_sessions;
	}

	public function &getSession($name, $default = null)
	{
		if (!isset($this->_sessions[$name])) {
			$this->_sessions[$name] = $default;
		}
		return $this->_sessions[$name];
	}

	public function importParams(array $params)
	{
		$this->_params = $params;
		return $this;
	}

	public function exportParams()
	{
		return $this->_params;
	}

	public function addParams(array $params, $namespace = 'index')
	{
		foreach ($params as $name => $value) {
			$this->setParam($name, $value, $namespace);
		}
		return $this;
	}

	public function setParam($name, $value, $namespace = 'index')
	{
		$this->_params[$namespace][$name] = $value;
		return $this;
	}

	public function mergeParams(array $params, $namespace = 'index')
	{
		foreach ($params as $name => $value) {
			$this->mergeParam($name, $value, $namespace);
		}
		return $this;
	}

	public function mergeParam($name, array $value, $namespace = 'index')
	{
		if (!isset($this->_params[$namespace][$name])) {
			$this->_params[$namespace][$name] = array();
		}
		$this->_params[$namespace][$name] = \Js\array_merge_smart(
			(array) $this->_params[$namespace][$name],
			$value
		);
		return $this;
	}

	public function getParams($namespace = 'index')
	{
		return isset($this->_params[$namespace])
			? $this->_params[$namespace]
			: array();
	}

	public function getParam($name, $default = null, $namespace = 'index')
	{
		return $this->hasParam($name, $namespace)
			? $this->_params[$namespace][$name]
			: $default;
	}

	public function hasParam($name, $namespace = 'index')
	{
		return isset($this->_params[$namespace][$name]);
	}

	public function addServers(array $servers)
	{
		foreach ($servers as $name => $value) {
			$this->setServer($name, $value);
		}
		return $this;
	}

	public function setServer($name, $value)
	{
		$this->_servers[$name] = $value;
		return $this;
	}

	public function getServers()
	{
		return $this->_servers;
	}

	public function getServer($name)
	{
		return isset($this->_servers[$name])
			? $this->_servers[$name]
			: null;
	}

	public function setProtocol($protocol)
	{
		$this->_protocol = $protocol;
		return $this;
	}

	public function getProtocol()
	{
		return $this->_protocol;
	}

	public function setMethod($method)
	{
		$this->_method = $method;
		return $this;
	}

	public function getMethod()
	{
		return $this->_method;
	}

	public function isHead()
	{
		return $this->getMethod() == self::METHOD_HEAD;
	}

	public function isGet()
	{
		return $this->getMethod() == self::METHOD_GET;
	}

	public function isPost()
	{
		return $this->getMethod() == self::METHOD_POST;
	}

	public function isPut()
	{
		return $this->getMethod() == self::METHOD_PUT;
	}

	public function isDelete()
	{
		return $this->getMethod() == self::METHOD_DELETE;
	}

	public function isAjax()
	{
		return $this->getHeader('X-Requested-With') == 'XMLHttpRequest';
	}

	public function addHeaders(array $headers)
	{
		foreach ($headers as $name => $value) {
			$this->setHeader($name, $value);
		}
		return $this;
	}

	public function setHeader($name, $value)
	{
		$this->_headers[strtolower($name)] = $value;
		return $this;
	}

	public function getHeaders()
	{
		return $this->_headers;
	}

	public function getHeader($name)
	{
		return $this->hasHeader($name)
			? $this->_headers[strtolower($name)]
			: null;
	}

	public function hasHeader($name)
	{
		return isset($this->_headers[strtolower($name)]);
	}

	public function setScheme($scheme)
	{
		$this->_scheme = $scheme;
		return $this;
	}

	public function getScheme()
	{
		return $this->_scheme;
	}

	public function setHost($host)
	{
		$this->_host = $host;
		return $this;
	}

	public function getHost()
	{
		return $this->_host;
	}

	public function setPort($port)
	{
		$this->_port = (int) $port;
		return $this;
	}

	public function getPort()
	{
		return $this->_port;
	}

	public function isPortDefault()
	{
		return ($this->getScheme() == self::SCHEME_HTTP && $this->getPort() == self::PORT_HTTP)
			|| ($this->getScheme() == self::SCHEME_HTTPS && $this->getPort() == self::PORT_HTTPS);
	}

	public function setBase($base)
	{
		$this->_base = $base;
		return $this;
	}

	public function getBase()
	{
		return $this->_base;
	}

	public function setRawQueryParams(array $params)
	{
		$this->_rawQueryParams = $params;
		return $this;
	}

	public function setPath($path)
	{
		$this->_path = $path;
		return $this;
	}

	public function getPath($base = false)
	{
		return $base
			? $this->getBase() . $this->_path
			: $this->_path;
	}

	public function getRawQueryParams()
	{
		return $this->_rawQueryParams;
	}

	public function addQueryParams(array $params)
	{
		foreach ($params as $name => $value) {
			$this->setQueryParam($name, $value);
		}
		return $this;
	}

	public function setQueryParam($name, $value)
	{
		$this->_queryParams[$name] = $value;
		$this->setParam($name, $value);
		return $this;
	}

	public function getQueryParams()
	{
		return $this->_queryParams;
	}

	public function getQueryParam($name, $default = null)
	{
		return $this->hasQueryParam($name)
			? $this->_queryParams[$name]
			: $default;
	}

	public function hasQueryParam($name)
	{
		return isset($this->_queryParams[$name]);
	}

	public function setRawPostParams(array $params)
	{
		$this->_rawPostParams = $params;
		return $this;
	}

	public function getRawPostParams()
	{
		return $this->_rawPostParams;
	}

	public function addPostParams(array $params)
	{
		foreach ($params as $name => $value) {
			$this->setPostParam($name, $value);
		}
		return $this;
	}

	public function setPostParam($name, $value)
	{
		$this->_postParams[$name] = $value;
		$this->setParam($name, $value);
		return $this;
	}

	public function getPostParams()
	{
		return $this->_postParams;
	}

	public function getPostParam($name, $default = null)
	{
		return $this->hasPostParam($name)
			? $this->_postParams[$name]
			: $default;
	}

	public function hasPostParam($name)
	{
		return isset($this->_postParams[$name]);
	}

	public function addCookies(array $cookies)
	{
		foreach ($cookies as $name => $value) {
			$this->setCookie($name, $value);
		}
		return $this;
	}

	public function setCookie($name, $value)
	{
		$this->_cookies[$name] = $value;
		$this->setParam($name, $value);
		return $this;
	}

	public function getCookies()
	{
		return $this->_cookies;
	}

	public function getCookie($name, $default = null)
	{
		return $this->hasCookie($name)
			? $this->_cookies[$name]
			: $default;
	}

	public function hasCookie($name)
	{
		return isset($this->_cookies[$name]);
	}

	public function getUrl()
	{
		$url = new \Js\Url();
		$url->setScheme($this->getScheme());
		$url->setHost($this->getHost());
		$url->setPort(!$this->isPortDefault() ? $this->getPort() : null);
		$url->setPath($this->getPath(true));
		$url->setQueryParams($this->getQueryParams(false));

		return $url;
	}
}