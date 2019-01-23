<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Filter\Rule;

use Coast;
use Coast\Filter\Rule;

class Trim extends Rule
{
    const MODE_BOTH  = 'trim';
    const MODE_LEFT  = 'ltrim';
    const MODE_RIGHT = 'rtrim';

    protected $_chars;

    protected $_mode;

	public function __construct($chars = null, $mode = self::MODE_BOTH)
	{
        $this->chars($chars);
		$this->mode($mode);
	}

    public function chars($chars = null)
    {
        if (func_num_args() > 0) {
            $this->_chars = $chars;
            return $this;
        }
        return $this->_chars;
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
        $args = [$value];
        if (isset($this->_chars)) {
            $args[] = $this->_chars;
        }
        return call_user_func_array($this->_mode, $args);
    }
}