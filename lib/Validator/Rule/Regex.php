<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Regex extends Rule
{
	protected $_regex;

	public function __construct($regex, $name = null)
	{
		$this->regex($regex);
		$this->name($name);
	}

    public function regex($regex = null)
    {
        if (func_num_args() > 0) {
            $this->_regex = $regex;
            return $this;
        }
        return $this->_regex;
    }

	protected function _validate($value)
	{
		if (!preg_match($this->_regex, $value)) {
			$this->error();
		}
	}
}
