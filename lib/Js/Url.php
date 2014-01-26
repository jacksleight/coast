<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js;

class Url
{
	const PART_SCHEME		= 0;
	const PART_USERNAME		= 1;
	const PART_PASSWORD		= 2;
	const PART_HOST			= 3;
	const PART_PORT			= 4;
	const PART_PATH			= 5;
	const PART_QUERY		= 6;
	const PART_FRAGMENT		= 7;
	
	const SCHEME_HTTP		= 'http';
	const SCHEME_HTTPS		= 'https';
	const SCHEME_MAILTO		= 'mailto';

	protected static $_colons = array(
		self::SCHEME_MAILTO	=> ':',
	);
	
	protected $_scheme;
	protected $_username;
	protected $_password;
	protected $_host;
	protected $_port;
	protected $_path;
	protected $_queryParams = array();
	protected $_fragment;
		
	public function __construct($string = null)
	{
		if (!isset($string)) {
			return;
		}
		
		$data = array_merge(array(
			'scheme'	=> null,
			'user'		=> null,
			'pass'		=> null,
			'host'		=> null,
			'port'		=> null,
			'path'		=> null,
			'query'		=> null,
			'fragment'	=> null,
		), parse_url($string));
		$this->setScheme($data['scheme']);
		$this->setUsername($data['user']);
		$this->setPassword($data['pass']);
		$this->setHost($data['host']);
		$this->setPort($data['port']);
		$this->setPath($data['path']);
		$this->setQuery($data['query']);
		$this->setFragment($data['fragment']);
	}
	
	public function toString($toPart = null, $fromStart = false)
	{
		$parts = array_fill(self::PART_SCHEME, self::PART_FRAGMENT + 1, null);
		
		if (isset($this->_scheme)) {
			$parts[self::PART_SCHEME]			= $this->getScheme();
			$parts[self::PART_SCHEME]		   .= isset(self::$_colons[$this->_scheme]) ? self::$_colons[$this->_scheme] : '://';
		} else if (isset($this->_host)) {
			$parts[self::PART_SCHEME]			= '//';
		}
		if (isset($this->_username)) {
			$parts[self::PART_USERNAME]			= $this->getUsername();
			if (isset($this->_password)) {
				$parts[self::PART_PASSWORD]		= ':' . $this->getPassword() . '@';
			} else {	
				$parts[self::PART_USERNAME]	   .= '@';
			}
		}
		if (isset($this->_host)) {
			$parts[self::PART_HOST]				= $this->getHost();
			if (isset($this->_port)) {
				$parts[self::PART_PORT]			= ':' . $this->getPort();
			}
		}
		if (isset($this->_path)) {
			$parts[self::PART_PATH]				= $this->getPath();
		}
		if (count($this->_queryParams) > 0) {
			$parts[self::PART_QUERY]			= '?' . $this->getQuery();
		}
		if (isset($this->_fragment)) {
			$parts[self::PART_FRAGMENT]			= '#' . $this->getFragment();
		}
		
		if (!isset($toPart)) {
			$toPart = $fromStart
				? self::PART_FRAGMENT
				: self::PART_SCHEME;
		}
		
		if ($fromStart) {
			$parts = array_slice($parts, self::PART_SCHEME, $toPart + 1);
		} else {
			$parts = array_slice($parts, $toPart);			
		}
		
		return implode(null, $parts);
	}

	public function __toString()
	{
		return $this->toString();
	}
	
	public function isHttp()
	{
		$scheme = strtolower($this->getScheme());
		return $scheme == self::SCHEME_HTTP || $scheme == self::SCHEME_HTTPS;
	}
	
	public function isHttps()
	{
		$scheme = strtolower($this->getScheme());
		return $scheme == self::SCHEME_HTTPS;
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
	
	public function setUsername($username)
	{
		$this->_username = $username;
		return $this;
	}
	
	public function getUsername()
	{
		return $this->_username;
	}
	
	public function setPassword($password)
	{
		$this->_password = $password;
		return $this;
	}
	
	public function getPassword()
	{
		return $this->_password;
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
		$this->_port = $port;
		return $this;
	}
	
	public function getPort()
	{
		return $this->_port;
	}
	
	public function setPath($path)
	{
		$this->_path = $path;
		return $this;
	}
	
	public function getPath()
	{
		return $this->_path;
	}
	
	public function setQuery($query)
	{
		parse_str($query, $params);
		$this->setQueryParams($params);
		return $this;
	}
	
	public function getQuery()
	{
		return http_build_query($this->getQueryParams());
	}
	
	public function setQueryParams(array $params)
	{
		$this->_queryParams = $params;
		return $this;
	}
	
	public function setQueryParam($name, $value)
	{
		$this->_queryParams[$name] = $value;
		return $this;
	}
		
	public function getQueryParams()
	{
		return $this->_queryParams;
	}
	
	public function getQueryParam($name)
	{
		return isset($this->_queryParams[$name])
			? $this->_queryParams[$name]
			: null;
	}
	
	public function setFragment($fragment)
	{
		$this->_fragment = $fragment;
		return $this;
	}
	
	public function getFragment()
	{
		return $this->_fragment;
	}
}