<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Filter\Rule;

use Coast\Filter\Rule;

class Custom extends Rule
{
    protected $_func = [];

	public function __construct(callable $func, $name = null)
	{
        $this->func($func);
        $this->name($name);
	}

    public function func($func = null)
    {
        if (func_num_args() > 0) {
            $this->_func = $func;
            return $this;
        }
        return $this->_func;
    }

    protected function _filter($value, $context = null)
    {
        $func = $this->_func;
        return $func($value, $this, $context);
    }
}