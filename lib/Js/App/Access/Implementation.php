<?php
/* 
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\App\Access;

trait Implementation
{
	protected $_app;

	public function setApp(\Js\App $app)
	{
		$this->_app = $app;
		return $this;
	}

	public function __get($name)
	{
		return $this->_app->__get($name);
	}

	public function __isset($name)
	{
		return $this->_app->__isset($name);
	}

	public function __call($name, array $args)
	{
		return $this->_app->__call($name, $args);
	}
}