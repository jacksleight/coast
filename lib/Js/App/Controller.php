<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\App;

class Controller implements \Js\App\Access, \Js\App\Routable
{
	use \Js\App\Access\Implementation;
	use \Js\Options;
	
	protected $_stack	= array();
	protected $_history	= array();
	protected $_actions	= array();

	public function __construct(array $options = array())
	{
		$this->setOptions(array_merge([
			'namespace'	=> null,
		], $options));
	}

	public function forward($action, $name = null)
	{
		if (count($this->_history) > 0) {
			$item	= $this->_history[count($this->_history) - 1];
			$name	= isset($name)
				? $name
				: $item[0];
			$params	= $item[2];
		}
		$this->_stack = array();
		$this->_queue($name, $action, $params);
	}

	public function dispatch($name, $action, $params = array())
	{
		$this->_stack = array();
		$this->_history = array();
		$this->_queue($name, $action, $params);

		$final = null;
		while (count($this->_stack) > 0) {
			$item = array_shift($this->_stack);
			$this->_history[] = $item;
			list($name, $action, $params) = $item;

			$class = $this->_options->namespace . '\\' . implode('\\', array_map('ucfirst', explode('_', $name)));
			if (!isset($this->_actions[$class])) {
				if (!class_exists($class)) {
					throw new \Js\App\Exception("Controller '{$name}' does not exist");
				}
				$object = new $class($this);
				if (!$object instanceof \Js\App\Controller\Action) {
					throw new \Js\App\Exception("Controller '{$name}' is not an instance of \Js\App\Controller\Action");
				}
				$this->_actions[$class] = $object;
			} else {
				$object = $this->_actions[$class];
			}

			if (!method_exists($object, $action)) {
				throw new \Js\App\Exception("Controller action '{$name}::{$action}' does not exist");
			}

			$result = call_user_func_array(array($object, $action), $params);
			if ($action != 'preDispatch' && $action != 'postDispatch') {
				$final = $result;
			}
		}

		return $final;
	}

	protected function _queue($name, $action, $params)
	{
		$parts	= explode('_', $name);
		$path	= array();
		$names	= array('all');
		while (count($parts) > 0) {
			$path[]	 = array_shift($parts);
			$names[] = implode('_', $path);
		}

		$final = $names[count($names) - 1];

		$stack = array();
		foreach ($names as $name) {
			$stack[] = array($name, 'preDispatch', $params);
		}
		$stack[] = array($final, $action, $params);
		foreach (array_reverse($names) as $name) {
			$stack[] = array($name, 'postDispatch', $params);
		}

		foreach ($stack as $item) {
			if (!in_array($item, $this->_history)) {
				$this->_stack[] = $item;
			}
		}
	}

	public function route(\Js\App\Request $req, \Js\App\Response $res)
	{		
		return $this->dispatch(
			$req->getParam('_controller'),
			$req->getParam('_action'),
			[$req, $res]
		);
	}
}