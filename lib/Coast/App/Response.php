<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class Response
{
	protected $_req;
	
	protected $_status	= null;
	protected $_headers	= array();
	protected $_cookies	= array();
	protected $_body	= '';

	public function __construct(\Coast\App\Request $req)
	{
		$this->_req = $req;
		$this->status(200);
	}

	public function request()
	{
		return $this->_req;
	}

	public function export()
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
			$this->_cookies[$name] = array($value, (isset($age) ? time() + $age : null), $path, $domain, $secure, $http);
			return $this;
		}
		return isset($this->_cookies[$name])
			? $this->_cookies[$name]
			: null;
	}

	public function body($value = null)
	{
		if (isset($value)) {
			$this->_body = $value;
			return $this;
		}
		return $this->_body;
	}

	public function text($value)
	{
		return $this
			->type('text/plain')
			->body((string) $value);
	}

	public function html($value)
	{
		return $this
			->type('text/html')
			->body((string) $value);
	}

	public function json($value)
	{
		return $this
			->type('application/json')
			->body(json_encode($value, JSON_PRETTY_PRINT));
	}

	public function xml($value)
	{
		if ($value instanceof \DOMDocument) {
			$value = $value->saveXML();
		} else if ($value instanceof \SimpleXMLElement) {
			$value = $value->asXML();
		} else {
			$value = (string) $value;
		}
		return $this
			->type('application/xml')
			->body($value);
	}

	public function redirect($type, $url)
	{
		return $this
			->status($type)
			->header('Location', $url);
	}
}