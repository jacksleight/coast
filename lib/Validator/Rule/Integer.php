<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Integer extends Rule
{
	protected function _validate($value)
	{
		if (strval(intval($value)) !== (string) $value) {
		 	$this->error();
		}
	}
}