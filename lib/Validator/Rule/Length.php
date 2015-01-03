<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Length extends Rule
{
	const MIN = 'min';
	const MAX = 'max';

	protected $_min = null;
	protected $_max = null;

	public function __construct($min = null, $max = null)
	{
		$this->_min = $min;
		$this->_max = $max;
	}

	protected function _validate($value)
	{
		$length = strlen($value);
		if (isset($this->_min) && $length < $this->_min) {
			$this->error(self::MIN);
		}
		if (isset($this->_max) && $length > $this->_max) {
			$this->error(self::MAX);
		}
	}
}
