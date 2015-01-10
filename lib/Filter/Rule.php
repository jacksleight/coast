<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Filter;

abstract class Rule
{
	protected $_name;

	public function __construct()
	{}
	
	public function name($name = null)
    {
        if (func_num_args() > 0) {
            $this->_name = $name;
            return $this;
        }
        if (isset($this->_name)) {
        	return $this->_name;
        }
        $parts = explode('\\', get_class($this));
		return lcfirst(array_pop($parts));
    }

	public function params()
	{
		$params = [];
		foreach (get_object_vars($this) as $key => $value) {
			if (in_array($key, ['_name', '_errors'])) {
				continue;
			}
			$params[ltrim($key, '_')] = $value;
		}
		return $params;
	}

	abstract protected function _filter($value);

	public function filter($value)
	{
		return $this->_filter($value);
	}

	public function __invoke($value)
	{
		return $this->filter($value);
	}
}