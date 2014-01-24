<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\Http;

class Response
{
	protected $_status;
	protected $_headers	= array();
	protected $_body;

	public function __construct($status, array $headers, $body)
	{
		$this->_status	= $status;
		$this->_body	= $body;
		foreach ($headers as $name => $value) {
			$this->_headers[strtolower($name)] = $value;
		}
	}

	public function getStatus()
	{
		return $this->_status;
	}

	public function getHeaders()
	{
		return $this->_headers;
	}

	public function getHeader($name)
	{
		return isset($this->_headers[strtolower($name)])
			? $this->_headers[strtolower($name)]
			: null;
	}

	public function getBody()
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