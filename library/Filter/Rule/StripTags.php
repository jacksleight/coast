<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Filter\Rule;

use Coast\Filter\Rule;

class StripTags extends Rule
{
	protected $_allowed;

	public function __construct($allowed = null)
	{
		$this->allowed($allowed);
	}

    public function allowed($allowed = null)
    {
        if (func_num_args() > 0) {
            $this->_allowed = $allowed;
            return $this;
        }
        return $this->_allowed;
    }

    protected function _filter($value)
    {
        return strip_tags($value, $this->_allowed);
    }
}