<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\App;

class Response
{
	protected $_req;
	
	protected $_status  = 200;
	protected $_headers = array();
	protected $_cookies = array();
	protected $_body = '';

	public function __construct(\Js\App\Request $req)
	{
		$this->_req = $req;
		$this->setStatus(200);
	}

	public function export()
	{
		if (session_status() == PHP_SESSION_ACTIVE) {
			$_SESSION = $this->_req->getSessions();
			session_write_close();
		}
		header($this->_req->getProtocol() . $this->_status);
		foreach ($this->_headers as $name => $value) {
			header("{$name}: {$value}");
		}
		foreach ($this->_cookies as $name => $params) {
			setcookie($name, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
		}
		echo $this->getBody();
	}

	public function setStatus($status)
	{
		$this->_status = $status;
		return $this;
	}

	public function setHeaders(array $headers)
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
		return isset($this->_headers[$name])
			? $this->_headers[$name]
			: null;
	}

	public function setCookies($cookies)
	{
		foreach ($cookies as $name => $params) {
			$this->setCookie($name, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
		}
		return $this;
	}

	public function setCookie($name, $value = null, $age = null, $path = null, $domain = null, $secure = false, $http = false)
	{
		if (!isset($path)) {
			$path = $this->_req->getBase();
		}
		$this->_cookies[$name] = array($value, (isset($age) ? time() + $age : null), $path, $domain, $secure, $http);
		return $this;
	}

	public function getCookies()
	{
		return $this->_cookies;
	}

	public function getCookie($name)
	{
		return isset($this->_cookies[$name])
			? $this->_cookies[$name]
			: null;
	}

	public function setBody($body)
	{
		$this->_body = $body;
		return $this;
	}

	public function getBody()
	{
		return $this->_body;
	}

	public function text($value)
	{
		$this
			->setHeader('Content-Type', 'text/plain')
			->setBody((string) $value);
		return $this;
	}

	public function html($value)
	{
		$this
			->setHeader('Content-Type', 'text/html')
			->setBody((string) $value);
		return $this;
	}

	public function json($value)
	{
		$this
			->setHeader('Content-Type', 'application/json')
			->setBody(json_encode($value));
		return $this;
	}

	public function xml(\DOMDocument $value)
	{
		$this
			->setHeader('Content-Type', 'application/xml')
			->setBody($value->saveXML());
		return $this;
	}

	public function redirect($type, $url)
	{
		$this
			->setStatus($type)
			->setHeader('Location', $url);
		return $this;
	}
}