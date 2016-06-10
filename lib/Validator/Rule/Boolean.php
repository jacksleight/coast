<?php
/* 
 * Copyright 2008-2013 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Boolean extends Rule
{
	protected function _validate($value)
	{
        $true  = [true, 'true', 1, '1'];
        $false = [false, 'false', 0, '0'];
		if (!in_array($value, $true, true) && !in_array($value, $false, true)) {
		 	$this->error();
		}
	}
}