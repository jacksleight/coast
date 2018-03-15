<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast;
use Coast\Transformer\Rule;

class ArrayType extends Rule
{
    protected function _transform($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        $value = strlen($value) > 0
            ? explode(',', $value)
            : [];
        return $value;
    }
}