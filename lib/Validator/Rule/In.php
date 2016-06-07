<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class In extends Rule
{
	protected $_values = [];

	public function __construct(array $values)
	{
		$this->values($values);
	}

    public function values($values = null)
    {
        if (func_num_args() > 0) {
            $this->_values = $values;
            return $this;
        }
        return $this->_values;
    }

	protected function _validate($value)
	{
		if (!in_array($value, $this->_values)) {
			$this->error();
		}
	}
}
