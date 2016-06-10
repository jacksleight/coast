<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer;

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

	abstract protected function _transform($value);

	public function transform($value)
	{
		return $this->_transform($value);
	}

	public function __invoke($value)
	{
		return $this->transform($value);
	}
}