<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js;

trait Options
{
	protected $_options = null;

	public function setOptions(array $options)
	{
		if (!isset($this->_options)) {
			$this->_options = new \stdClass();
		}
		foreach ($options as $name => $value) {
			$this->_options->$name = isset($value)
				? $this->_initOption($name, $value)
				: $value;
		}
	}

	protected function _initOption($name, $value)
	{
		return $value;
	}
}