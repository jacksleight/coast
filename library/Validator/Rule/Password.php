<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Password extends Rule
{
	const LOWER		= 'lower';
	const UPPER		= 'upper';
	const DIGIT		= 'digit';
	const SPECIAL	= 'special';

	protected $_lower	= 0;
	protected $_upper	= 0;
	protected $_digit	= 0;
	protected $_special	= 0;

	public function __construct($lower = 0, $upper = 0, $digit = 0, $special = 0)
	{
		$this->lower($lower);
		$this->upper($upper);
		$this->digit($digit);
		$this->special($special);
	}

    public function lower($lower = null)
    {
        if (func_num_args() > 0) {
            $this->_lower = $lower;
            return $this;
        }
        return $this->_lower;
    }

    public function upper($upper = null)
    {
        if (func_num_args() > 0) {
            $this->_upper = $upper;
            return $this;
        }
        return $this->_upper;
    }

    public function digit($digit = null)
    {
        if (func_num_args() > 0) {
            $this->_digit = $digit;
            return $this;
        }
        return $this->_digit;
    }

    public function special($special = null)
    {
        if (func_num_args() > 0) {
            $this->_special = $special;
            return $this;
        }
        return $this->_special;
    }

	protected function _validate($value)
	{
		preg_match_all('/[a-z]/', $value, $matches);
		if (count($matches[0]) < $this->_lower) {
			$this->error(self::LOWER);
		}
		preg_match_all('/[A-Z]/', $value, $matches);
		if (count($matches[0]) < $this->_upper) {
			$this->error(self::UPPER);
		}
		preg_match_all('/[0-9]/', $value, $matches);
		if (count($matches[0]) < $this->_digit) {
			$this->error(self::DIGIT);
		}
		preg_match_all('/[^a-zA-Z0-9]/', $value, $matches);
		if (count($matches[0]) < $this->_special) {
			$this->error(self::SPECIAL);
		}
	}
}
