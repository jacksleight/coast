<?php
/* 
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js;

class Config
{
	protected $_data = array();

	public function __construct($files)
	{
		if (!is_array($files)) {
			$files = [$files];
		}
		foreach ($files as $file) {
			$data = array();
			require (string) $file;
			$this->_data = array_merge_recursive(
				$this->_data,
				$data
			);
		}
	}

	public function __set($name, $value)
	{
		$this->_data[$name] = $value;
	}

	public function __get($name)
	{
		return isset($this->_data[$name])
			? $this->_data[$name]
			: null;
	}

	public function __unset($name)
	{
		unset($this->_data[$name]);
	}

	public function __isset($name)
	{
		return isset($this->_data[$name]);
	}
}