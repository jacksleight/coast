<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
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