<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class IpAddress extends Rule
{
	protected function _validate($value)
	{
		if (!filter_var($value, FILTER_VALIDATE_IP)) {
		 	$this->error();
		}
	}
}