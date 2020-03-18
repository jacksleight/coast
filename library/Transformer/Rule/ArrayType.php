<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast;
use Coast\Transformer\Rule;

class ArrayType extends Rule
{
    protected $_delimiter;

    public function __construct($delimiter = ',')
    {
        $this->delimiter($delimiter);
    }

    public function delimiter($delimiter = null)
    {
        if (func_num_args() > 0) {
            $this->_delimiter = $delimiter;
            return $this;
        }
        return $this->_delimiter;
    }

    protected function _transform($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        $value = strlen($value) > 0
            ? explode($this->_delimiter, $value)
            : [];
        return $value;
    }
}