<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast;
use Coast\Transformer\Rule;

class Null extends Rule
{
    protected function _transform($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        return strlen($value)
        	? $value
        	: null;
    }
}