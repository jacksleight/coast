<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Filter\Rule;

use Coast;
use Coast\Filter\Rule;

class CamelCaseSplit extends Rule
{
    protected $_space;

    public function __construct($space = ' ')
    {
        $this->space($space);
    }

    public function space($space = null)
    {
        if (func_num_args() > 0) {
            $this->_space = $space;
            return $this;
        }
        return $this->_space;
    }

    protected function _filter($value)
    {
        return Coast\str_camel_split($value, $this->_space);
    }
}