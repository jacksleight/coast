<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Filter\Rule;

use Coast\Filter\Rule;

class SanitizeUrl extends Rule
{
    protected function _filter($value)
    {
        return filter_var($value, FILTER_SANITIZE_URL);
    }
}