<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Count extends Rule
{
	const MIN = 'min';
	const MAX = 'max';

	protected $_min = null;
	protected $_max = null;

	public function __construct($min = null, $max = null)
	{
		$this->min($min);
		$this->max($max);
	}

    public function min($min = null)
    {
        if (func_num_args() > 0) {
            $this->_min = $min;
            return $this;
        }
        return $this->_min;
    }

    public function max($max = null)
    {
        if (func_num_args() > 0) {
            $this->_max = $max;
            return $this;
        }
        return $this->_max;
    }

	protected function _validate($value)
	{
		$count = count($value);
		if (isset($this->_min) && $count < $this->_min) {
			$this->error(self::MIN);
		}
		if (isset($this->_max) && $count > $this->_max) {
			$this->error(self::MAX);
		}
	}
}