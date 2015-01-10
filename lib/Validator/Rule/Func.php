<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Func extends Rule
{
	protected $_func;

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

	protected function _validate($value)
	{
		if (!call_user_func($this->_func, $value)) {
			$this->error();
		}
	}
}
