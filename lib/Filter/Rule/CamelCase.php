<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Filter\Rule;

use Coast;
use Coast\Filter\Rule;

class CamelCase extends Rule
{
	const MODE_LOWER = 'Coast\str_camel_lower';
	const MODE_UPPER = 'Coast\str_camel_upper';

    protected $_mode;

	public function __construct($mode = self::MODE_LOWER)
	{
		$this->mode($mode);
	}

    public function mode($mode = null)
    {
        if (func_num_args() > 0) {
            $this->_mode = $mode;
            return $this;
        }
        return $this->_mode;
    }

    protected function _filter($value)
    {
        return call_user_func($this->_mode, $value);
    }
}