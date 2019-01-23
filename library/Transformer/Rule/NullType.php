<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast;
use Coast\Transformer\Rule;

class NullType extends Rule
{
    protected function _transform($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        if (strlen($value) == 0) {
            $value = null;
        }
        return $value;
    }
}