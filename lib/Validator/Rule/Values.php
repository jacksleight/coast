<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Values extends Rule
{
	protected $_values = [];

	public function __construct(array $values)
	{
		$this->_values = $values;
	}

	protected function _validate($value)
	{
		if (!in_array($value, $this->_values)) {
			$this->error();
		}
	}
}
