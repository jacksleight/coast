<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class App
{
	const MODE_CLI	= 'cli';
	const MODE_HTTP	= 'http';
	
	protected $_envs	= [];
	protected $_params	= [];
	protected $_stack	= [];

	protected $_notFoundHandler;
	protected $_errorHandler;

	public function __construct(array $envs = array())
	{
		$this->_envs = array_merge(array(
			'MODE' => isset($_SERVER['HTTP_HOST']) ? self::MODE_HTTP : self::MODE_CLI,
		), $_ENV, $envs);

		date_default_timezone_set('UTC');
		$this->set('app', $this);
	}

	public function dir()
	{
		return $this->_dir;
	}

	public function env($name)
	{
		return isset($this->_envs[$name])
			? $this->_envs[$name]
			: null;
	}

	public function mode()
	{
		return $this->env('MODE');
	}

	public function http()
	{
		return $this->mode() == self::MODE_HTTP;
	}

	public function cli()
	{
		return $this->mode() == self::MODE_CLI;
	}

	public function add($name, $value = null)
	{
		if (!isset($value)) {
			$value = $name;
			$name = null;
		}
		if (!$value instanceof \Closure && !$value instanceof \Coast\App\Executable) {
			throw new \Coast\App\Exception("Param '{$name}' is not a closure or instance of Coast\App\Executable");
		}
		array_push($this->_stack, $value instanceof \Closure
			? $value
			: [$value, 'execute']);
		if (isset($name)) {
			$this->set($name, $value);
		}
		return $this;
	}

	public function set($name, $value)
	{
		if ($value instanceof \Coast\App\Access) {
			$value->app($this);
		}
		$this->_params[$name] = $value;
		return $this;
	}

	public function get($name)
	{
		return isset($this->_params[$name])
			? $this->_params[$name]
			: null;
	}

	public function has($name)
	{
		return isset($this->_params[$name]);
	}

	public function execute(\Coast\App\Request $req)
	{
		$res = $req->response();

		$this->set('req', $req)
			 ->set('res', $res);
		try {
			$result = null;
			foreach($this->_stack as $item) {
				$result = call_user_func($item, $req, $res, $this);
				if (isset($result)) {
					break;
				}
			}
			if ((bool) $result !== true) {
				if (isset($this->_notFoundHandler)) {
					call_user_func($this->_notFoundHandler, $req, $res, $this);
				} else {
					throw new \Coast\App\Exception('Nothing successfully handled the request');
				}
			}
		} catch (\Exception $e) {
			if (isset($this->_errorHandler)) {
				call_user_func($this->_errorHandler, $req, $res, $this, $e);
			} else {
				throw $e;
			}
		}
		$this->set('req', null)
			 ->set('res', null);
		
		return $res;
	}

	public function notFoundHandler(\Closure $notFoundHandler)
	{
		$this->_notFoundHandler = $notFoundHandler;
		return $this;
	}

	public function errorHandler(\Closure $errorHandler)
	{
		$this->_errorHandler = $errorHandler;
		return $this;
	}

	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	public function __get($name)
	{
		return $this->get($name);
	}

	public function __call($name, array $args)
	{
		$value = $this->get($name);
		if (!is_object($value) || !method_exists($value, 'call')) {
			throw new \Coast\App\Exception("Param '{$name}' is not an object or does not have a call method");
		}
		return call_user_func_array(array($value, 'call'), $args);
	}

	public function __isset($name)
	{
		return $this->has($name);
	}
}