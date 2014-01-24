<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\App\View;

class Content
{
	protected $_data = array();

	public function __construct(array $data = array())
	{
		$this->_data = $data;
	}

	public function add($value, $name = null)
	{
		if (isset($name)) {
			$this->_data[$name] = $value;
		} else {
			$this->_data[] = $value;
		}
		return $this;
	}

	public function has($name)
	{
		return isset($this->_data[$name]);
	}

	public function get($name)
	{
		return isset($this->_data[$name])
			? $this->_data[$name]
			: null;
	}

	public function toString()
	{
		return implode($this->_data);
	}

	public function __isset($name)
	{
		return $this->has($name);
	}

	public function __get($name)
	{
		return $this->get($name);
	}

	public function __toString()
	{
		return $this->toString();
	}
}