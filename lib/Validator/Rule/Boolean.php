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
		if (!filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
		 	$this->error();
		}
	}
}