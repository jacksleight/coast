<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator;

abstract class Rule
{
	protected $_errors = [];

	public function name()
	{
		$parts = explode('\\', get_class($this));
		return lcfirst(array_pop($parts));
	}

	abstract protected function _validate($value);

	public function validate($value)
	{
		$this->_errors = [];
		$this->_validate($value);
		return !count($this->_errors);
	}

	public function __invoke($value)
	{
		return $this->validate($value);
	}

	public function error($name = null)
	{
		$params = [];
		foreach (get_object_vars($this) as $key => $value) {
			if ($key == '_errors') {
				continue;
			}
			$params[ltrim($key, '_')] = $value;
		}
		$this->_errors[] = [$this->name(), $name, $params];
		return $this;
	}

	public function errors()
	{
		return $this->_errors;
	}
}