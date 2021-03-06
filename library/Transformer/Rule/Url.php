<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast\Url as CoastUrl;
use Coast\Transformer\Rule;

class Url extends Rule
{
    protected function _transform($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }
        return new CoastUrl($value);
    }
}