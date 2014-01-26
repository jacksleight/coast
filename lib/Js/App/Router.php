<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js\App;

class Router implements \Js\App\Access, \Js\App\Executable
{
	use \Js\App\Access\Implementation;

	protected $_target = array();
	protected $_routes = array();

	public function __construct(\Js\App\Routable $target)
	{
		$this->_target = $target;
	}

	public function app(\Js\App $app)
	{
		$this->_app = $app;
		if ($this->_target instanceof \Js\App\Access) {
			$this->_target->app($app);
		}
		return $this;
	}

	public function add($name, $path, array $params = array(), array $rules = array())
	{
		$parts = explode('/', trim($path, '/'));
		$names = array();
		$regex = array();
		foreach ($parts as $i => $part) {
			if (preg_match('/^:(.+)$/', $part, $matches)) {
				$name = $matches[1];
				$names[] = $name;
				$match = isset($rules[$name])
					? "({$rules[$name]})"
					: '([^\/]+)';
				if ($i > 0) {
					$match = "\/{$match}";
				}
				$match = "(?:$match)";
				if (array_key_exists($name, $params)) {
					$match .= '?';
				}
			} else {
				$match = preg_quote($part, '/');
				if ($i > 0) {
					$match = "\/{$match}";
				}
			}
			$regex[$i] = $match;
		}
		$regex = '/^' . ltrim(implode(null, $regex), '\/') . '$/';

		$this->_routes[$name] = [
			'path'		=> $path,
			'params'	=> $params,
			'rules'		=> $rules,
			'names'		=> $names,
			'regex'		=> $regex,
		];
	}

	public function match($path)
	{
		$path = trim($path, '/');
		foreach ($this->_routes as $name => $route) {
			if (!preg_match($route['regex'], $path, $match)) {
				continue;
			}
			array_shift($match);	
			$params = array_merge(
				$route['params'],
				count($match) > 0
					? array_combine(array_slice($route['names'], 0, count($match)), $match)
					: array()
			);
			return [
				'name'	 => $name,
				'params' => $params,
			];
		}		
		return false;
	}

	public function reverse($name, array $params = array())
	{
		if (!isset($this->_routes[$name])) {
			throw new \Js\App\Exception("Route '{$name}' does not exist");
		}
		
		$route	= $this->_routes[$name];
		$parts	= explode('/', $route['path']);
		$path	= array();
		foreach ($parts as $i => $part) {
			if (preg_match('/^:(.+)$/', $part, $matches)) {
				$name = $matches[1];
				if (isset($params[$name])) {
					$value = $params[$name];
				} elseif (array_key_exists($name, $route['params'])) {
					$value = null;
				} else {
					throw new \Exception("Parameter '{$name}' missing");
				}
			} else {
				$value = $part;
			}
			$path[$i] = $value;
		}
		while (count($path) > 0 && !isset($path[count($path) - 1])) {
			array_pop($path);
		}
		return implode('/', $path);
	}

	public function execute(\Js\App\Request $req, \Js\App\Response $res)
	{
		$match = $this->match($req->getPath());
		if (!$match) {
			return false;
		}
		$req->addParams(array_merge([
			'_route' => $match,
		], $match['params']));
		return $this->_target->route($req, $res);
	}
}