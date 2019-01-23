<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast\Transformer\Rule;

class IntegerType extends Rule
{
    protected function _transform($value)
    {
        if (is_numeric($value)) {
            $value = (int) $value;
        }
        return $value;
    }
}