<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
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
			if ($func instanceof \Closure) {
				$func = $func->bindTo($this);
			}
            $this->_func = $func;
            return $this;
        }
        return $this->_func;
    }

	protected function _validate($value, $context = null)
	{
		if (call_user_func($this->_func, $value, $context) === false) {
			$this->error();
		}
	}
}
