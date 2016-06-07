<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Not extends Rule
{
	protected $_validator;

	public function __construct(Rule $validator)
	{
		$this->_validator = $validator;
	}

	public function params()
	{
		return $this->_validator->params();
	}

	protected function _validate($value)
	{
		if ($this->_validator->validate($value)) {
			$this->error($this->_validator->name());
		}
	}
}
