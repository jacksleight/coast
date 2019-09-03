<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule\Model;

use Coast\Model;
use Coast\Transformer\Rule;

class One extends Rule
{
    protected $_property = [];

    protected $_className = [];

    public function __construct($property, $className)
    {
        $this->property($property);
        $this->className($className);
    }

    public function property($property = null)
    {
        if (func_num_args() > 0) {
            $this->_property = $property;
            return $this;
        }
        return $this->_property;
    }

    public function className($className = null)
    {
        if (func_num_args() > 0) {
            $this->_className = $className;
            return $this;
        }
        return $this->_className;
    }

    protected function _transform($value, $context = null)
    {
        if (is_object($value)) {
            return $value;
        }
        return Model::modelFetch($this->_className, $id);
    }
}