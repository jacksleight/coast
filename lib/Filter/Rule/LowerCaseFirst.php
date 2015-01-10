<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Filter\Rule;

use Coast;
use Coast\Filter\Rule;

class LowerCaseFirst extends Rule
{
    protected function _filter($value)
    {
        return lcfirst($value);
    }
}