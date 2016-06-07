<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Max extends Rule
{
	protected $_value = [];

	public function __construct($value)
	{
		$this->value($value);
	}

    public function value($value = null)
    {
        if (func_num_args() > 0) {
            $this->_value = $value;
            return $this;
        }
        return $this->_value;
    }

	protected function _validate($value)
	{
		if ($value > $this->_value) {
			$this->error();
		}
	}
}
