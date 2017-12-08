<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast\Transformer\Rule;

class BooleanType extends Rule
{
    protected $_true = [true, 'true', 1, '1'];

    protected $_false = [false, 'false', 0, '0'];

    public function __construct(array $true = null, array $false = null)
    {
        if (isset($true)) {
            $this->true($true);
        }
        if (isset($false)) {
            $this->false($false);
        }
    }

    public function true(array $true = null)
    {
        if (func_num_args() > 0) {
            $this->_true = $true;
            return $this;
        }
        return $this->_true;
    }

    public function false(array $false = null)
    {
        if (func_num_args() > 0) {
            $this->_false = $false;
            return $this;
        }
        return $this->_false;
    }

    protected function _transform($value)
    {
        if (in_array($value, $this->_true, true)) {
            $value = true;
        }
        if (in_array($value, $this->_false, true)) {
            $value = false;
        }
        return $value;
    }
}