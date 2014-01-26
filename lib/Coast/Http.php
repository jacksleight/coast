<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Http
{
	const METHOD_HEAD	= 'HEAD';
	const METHOD_GET	= 'GET';
	const METHOD_POST	= 'POST';
	
	protected $_timeout;
	protected $_cookie;
	
	public function __construct($timeout = null)
	{
		$this->_timeout = $timeout;
	}
	
	public function request(\Coast\Url $url, $method = self::METHOD_GET, $data = null)
	{
		if (!$url->isHttp()) {
			throw new \Exception("URL scheme is not HTTP or HTTPS");
		}
		
		$ch = curl_init($url->string());
		curl_setopt($ch, CURLOPT_HEADER, true);
		if (!ini_get('open_basedir')) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if (isset($this->_timeout)) {
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
		}
		if (isset($this->_cookie)) {
			curl_setopt($ch, CURLOPT_COOKIE, $this->_cookie);
		}
		if ($method == self::METHOD_HEAD) {
			curl_setopt($ch, CURLOPT_NOBODY, true);
		} elseif ($method == self::METHOD_POST) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data instanceof \Coast\File
				? '@' . $data->string()
				: $data);
		}
		
		$response = curl_exec($ch);
		
		$status	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$size	= curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$head	= substr($response, 0, $size);
		$body	= $method != self::METHOD_HEAD
			? substr($response, $size)
			: null;
		
		$headers = array();
		if ($head) {
			$parts	= explode("\r\n\r\n", $head);
			$head	= $parts[count($parts) - 2];
			$head	= explode("\r\n", $head);
			$headers= array();
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
			$this->_cookie = $headers['cookie'];
		}
		
		curl_close($ch);
		return new \Coast\Http\Response($status, $headers, $body);
	}
}