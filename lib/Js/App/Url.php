<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js\App;

class Url implements \Js\App\Access
{
	use \Js\App\Access\Implementation;
	use \Js\Options;

	public function __construct(array $options = array())
	{
		$this->options(array_merge([
			'base'		=> '/',
			'dir'		=> '',
			'cdnBase'	=> null,
			'version' 	=> false,
			'router' 	=> null,
		], $options));
	}

	protected function _initialize($name, $value)
	{
		switch ($name) {
			case 'base':
			case 'cdnBase':
				$value = new \Js\Url("{$value}");
				break;
			case 'dir':
				$value = new \Js\Dir("{$value}");
				$value = $value->isRelative()
					? new \Js\Dir(getcwd() . "/{$value}")
					: $value;
				break;
		}
		return $value;
	}

	public function call()
	{
		$args = func_get_args();
		if (!isset($args[0])) {
			$method = 'base';
		} else if (is_array($args[0])) {
			$method = 'route';
		} else if ($args[0] instanceof \Js\Url) {
			$method = 'url';
		} else if ($args[0] instanceof \Js\File) {
			$method = 'file';
		} else {
			$method = 'string';
		}
		return call_user_func_array(array($this, $method), $args);
	}

	public function base()
	{
		return $this->_options->base->string();
	}

	public function string($string, $base = true)
	{
		$path = (string) $string;
		return $base
			? $this->_options->base . $path
			: $path;
	}

	public function route(array $params = array(), $name = null, $reset = false, $base = true)
	{
		if (!isset($this->_options->router)) {
			throw new \Js\App\Exception("Router option has not been set");
		}
		$route = isset($this->req)
			? $this->req->getParam('_route')
			: null;
		if (!isset($name)) {
			if (!isset($route)) {
				throw new \Js\App\Exception("Route not specified and no previous route is avaliable");
			}
			$name = $route['name'];
		}
		if (!$reset && isset($route)) {
			$params = array_merge(
				$route['params'],
				$params
			);
		}
		$path = $this->_options->router->reverse($name, $params);
		return $base
			? $this->_options->base . $path
			: $path;
	}

	public function url(\Js\Url $url, $toPart = null, $fromTop = false)
	{
		return $url->string($toPart, $fromTop);
	}

	public function file($file, $base = true, $cdn = true)
	{
		$file = new \Js\File("{$file}");
		$file = $file->isRelative()
			? new \Js\File(getcwd() . "/{$file}")
			: $file;
		if (!$file->isWithin($this->_options->dir)) {
			throw new \Js\App\Exception("File '{$file}' is not within base directory");
		}

		if ($this->_options->version && $file->isFile()) {
			$time = $file->getModifyTime()->format('U');
			$info = $file->string(\Js\Path::ALL);
			$info['dirname'] = $info['dirname'] != '.'
				? "{$info['dirname']}/"
				: '';
			$file = new \Js\File("{$info['dirname']}{$info['filename']}.{$time}.{$info['extension']}");
		}

		$path = $this->_options->dir->toRelative($file);
		if ($base) {
			$path = $cdn && isset($this->_options->cdnBase)
				? $this->_options->cdnBase . $path
				: $this->_options->base . $path;
		}
		return $path;
	}

	public function query(array $params = array(), $reset = false, $mark = true, $contract = true)
	{
		$params = $this->_parseQueryParams($params, $reset, $contract);
		$query  = array();
		foreach ($params as $name => $value) {
			$query[] = $name . '=' . urlencode($value);
		}
		$query = implode('&', $query);
		
		return $mark
			? '?' . $query
			: $query;
	}

	public function inputs(array $params = array(), $reset = false, $contract = true)
	{
		$params = $this->_parseQueryParams($params, $reset, $contract);
		$inputs = array();
		foreach ($params as $name => $value) {
			$inputs[] = '<input type="hidden" name="' . $name . '" value="' . $value . '">';
		}
		
		return implode($inputs);
	}

	protected function _parseQueryParams(array $params = array(), $reset = false, $contract = true)
	{
		if (!$reset && isset($this->req)) {
			$params = \Js\array_merge_smart(
				$this->req->getQueryParams(),
				$params
			);
		}
		$params = \Js\array_filter_null_recursive($params);
		
		return $contract
			? \Js\array_contract($params, '_')
			: $params;
	}
}