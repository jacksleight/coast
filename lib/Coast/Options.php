<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

trait Options
{
	protected $_options = null;

	public function options(array $options = null)
	{
		if (!isset($this->_options)) {
			$this->_options = new \stdClass();
		}
		if (isset($options)) {
			foreach ($options as $name => $value) {
				$this->_options->$name = isset($value)
					? $this->_initialize($name, $value)
					: $value;
			}
		}
		return $this->_options;
	}

	protected function _initialize($name, $value)
	{
		return $value;
	}
}