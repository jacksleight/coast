<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Value extends Rule
{
	protected $_value = [];

	public function __construct($value)
	{
		$this->_value = $value;
	}

	protected function _validate($value)
	{
		if ($value != $this->_value) {
			$this->error();
		}
	}
}
