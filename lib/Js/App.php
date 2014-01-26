<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js;

class App
{
	const MODE_CLI	= 'cli';
	const MODE_HTTP	= 'http';

	const SERVER_DEVELOPMENT	= 'development';
	const SERVER_STAGING		= 'staging';
	const SERVER_PRODUCTION		= 'production';
	
	protected static $_errorLevels = array (
		E_ERROR				=> 'Fatal Error',
		E_WARNING			=> 'Warning',
		E_PARSE				=> 'Parsing Error',
		E_NOTICE			=> 'Notice',
		E_CORE_ERROR		=> 'Core Error',
		E_CORE_WARNING		=> 'Core Warning',
		E_COMPILE_ERROR		=> 'Compile Error',
		E_COMPILE_WARNING	=> 'Compile Warning',
		E_USER_ERROR		=> 'User Error',
		E_USER_WARNING		=> 'User Warning',
		E_USER_NOTICE		=> 'User Notice',
		E_STRICT			=> 'Runtime Notice',
		E_RECOVERABLE_ERROR	=> 'Catchable Fatal Error',
		E_DEPRECATED		=> 'Deprecated',
		E_USER_DEPRECATED	=> 'User Deprecated',
	);

	protected $_envs	= array();
	protected $_params	= array();
	protected $_stack	= array();

	protected $_notFoundHandler;
	protected $_errorHandler;
	protected $_errorLog;

	public function __construct(array $envs = array())
	{
		$this->_envs = array_merge(array(
			'MODE'	 => isset($_SERVER['HTTP_HOST']) ? self::MODE_HTTP : self::MODE_CLI,
			'SERVER' => self::SERVER_DEVELOPMENT,
			'DEBUG'	 => true,
		), $_ENV, $envs);
		if ($this->debug()) {
			foreach ($_REQUEST as $name => $value) {
				if (preg_match('/^_debug-([a-z]+)$/', $name, $match)) {
					$this->setEnv(strtoupper(str_replace('-', '_', $name)), true);
				}
			}
		}

		error_reporting(E_ALL | E_STRICT);
		ini_set('display_errors', false);
		ini_set('log_errors', false);
		set_error_handler(array($this, 'onError'));
		set_exception_handler(array($this, 'onException'));
		register_shutdown_function(array($this, 'onShutdown'));
		
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

	public function server()
	{
		return $this->env('SERVER');
	}

	public function development()
	{
		return $this->server() == self::SERVER_DEVELOPMENT;
	}

	public function staging()
	{
		return $this->server() == self::SERVER_STAGING;
	}

	public function production()
	{
		return $this->server() == self::SERVER_PRODUCTION;
	}

	public function debug($type = null)
	{
		return $this->env(isset($type)
			? "DEBUG_" . strtoupper($type)
			: 'DEBUG');
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
		if (!$value instanceof \Closure && !$value instanceof \Js\App\Executable) {
			throw new \Js\App\Exception("Param '{$name}' is not a closure or instance of Js\App\Executable");
		}
		array_push($this->_stack, $value instanceof \Closure
			? $value
			: array($value, 'execute'));
		if (isset($name)) {
			$this->set($name, $value);
		}
		return $this;
	}

	public function set($name, $value)
	{
		if ($value instanceof \Js\App\Access) {
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

	public function execute(\Js\App\Request $req)
	{
		$res = $req->getResponse();

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
					throw new \Js\App\Exception('Nothing successfully handled the request');
				}
			}
		} catch (\Exception $e) {
			if (!$this->debug() && isset($this->_errorHandler)) {
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

	public function errorLog($errorLog)
	{
		$this->_errorLog = new \Js\File\Data\Log(getcwd() . "/{$errorLog}");
		if (!$this->_errorLog->getDir()->isDir()) {
			$this->_errorLog->getDir()->make(0777);
		}
		return $this;
	}
		
	public function error($type, $message = null, $file = null, $line = null, $trace = null)
	{
		if ($type instanceof \Exception) {
			$e = $type;
			$type = $e instanceof \ErrorException
				? get_class($e) . '/' . self::$_errorLevels[$e->getSeverity()]
				: get_class($e);
			return $this->error($type, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
		}

		$error = "{$type}: '{$message}' in {$file} on line {$line}";
		if (isset($trace)) {
			$error .= str_replace("\n", "\n\t", "\n{$trace}");
		}
		if ($this->debug()) {
			if (!in_array('Content-Type: text/plain', headers_list())) {
				echo "<pre>{$error}</pre>";
			} else {
				echo "{$error}\n";
			}
		} elseif (isset($this->_errorLog)) {
			$this->_errorLog
				->open('a+', 'Js\File\Data\Log')
				->add($error)
				->close();
		}
	}

	public function onError($level, $message, $file, $line)
	{
		if ($level & error_reporting()) {
			if ($level & E_NOTICE) {
				$this->error(self::$_errorLevels[$level], $message, $file, $line);
			} else {
				throw new \ErrorException($message, 0, $level, $file, $line);
			}
		}
	}

	public function onException(\Exception $e)
	{
		$this->error($e);
	}

	public function onShutdown()
	{
		$error = error_get_last();
        if (isset($error)) {
			$this->error(self::$_errorLevels[E_ERROR], $error['message'], $error['file'], $error['line']);
        }
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
			throw new \Js\App\Exception("Param '{$name}' is not an object or does not have a call method");
		}
		return call_user_func_array(array($value, 'call'), $args);
	}

	public function __isset($name)
	{
		return $this->has($name);
	}
}