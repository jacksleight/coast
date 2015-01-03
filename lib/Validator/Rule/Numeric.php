<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Numeric extends Rule
{
	protected function _validate($value)
	{
		if (!is_numeric($value)) {
		 	$this->error();
		}
	}
}