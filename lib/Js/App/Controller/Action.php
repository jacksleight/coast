<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\App\Controller;

abstract class Action
{
	protected $_controller;

	public function __construct(\Js\App\Controller $controller)
	{
		$this->_controller = $controller;
	}

	public function __get($name)
	{
		return $this->_controller->$name;
	}

	public function __isset($name)
	{
		return isset($this->_controller->$name);
	}

	public function __call($name, array $args)
	{
		return call_user_func_array(array($this->_controller, $name), $args);
	}

	public function preDispatch()
	{}

	public function postDispatch()
	{}
}