<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast\Transformer\Rule;

class Boolean extends Rule
{
    protected function _transform($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        $true  = [true, 'true', 1, '1'];
        $false = [false, 'false', 0, '0'];
        if (in_array($value, $true, true)) {
            return true;
        }
        if (in_array($value, $false, true)) {
            return false;
        }
        return $value;
    }
}