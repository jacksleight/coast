<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Model;

use Exception;
use Coast;
use Coast\Filter;
use Coast\Validator;

class Metadata 
{
    protected $_name;

    protected $_properties = [];

    public function __construct($name)
    {
        $this->_name = $name;
    }

    public function name()
    {
        return $this->_name;
    }

    public function property($name, array $value = null)
    {
        if (!property_exists($this->_name, $name)) {
            throw new Exception("Property '{$name}' is not defined in class '{$this->_name}'");  
        }
        if (func_num_args() > 1) {
            if (!isset($this->_properties[$name])) {
                $this->_properties[$name] = [
                    'name'      => $name,
                    'type'      => null,
                    'class'     => null,
                    'filter'    => new Filter(),
                    'validator' => new Validator(),
                ];
            }
            $value = Coast\array_merge_smart($this->_properties[$name], $value);
            if (isset($value['filterBefore'])) {
                $value['filter']->steps($value['filterBefore']->steps(), 0);
                unset($value['filterBefore']);
            }
            if (isset($value['filterAfter'])) {
                $value['filter']->steps($value['filterAfter']->steps());
                unset($value['filterAfter']);
            }
            if (isset($value['validatorBefore'])) {
                $value['validator']->steps($value['validatorBefore']->steps(), 0);
                unset($value['validatorBefore']);
            }
            if (isset($value['validatorAfter'])) {
                $value['validator']->steps($value['validatorAfter']->steps());
                unset($value['validatorAfter']);
            }
            $this->_properties[$name] = $value;
            return $this;
        }
        return $this->_properties[$name];
    }

    public function properties(array $properties = null)
    {
        if (func_num_args() > 0) {
            foreach ($properties as $name => $value) {
                $this->property($name, $value);
            }
            return $this;
        }
        return $this->_properties;
    }

    public function __clone()
    {
        foreach ($this->_properties as $name => $value) {
            if (isset($value['filter'])) {
                $value['filter'] = clone $value['filter'];
            }
            if (isset($value['validator'])) {
                $value['validator'] = clone $value['validator'];
            }
            $this->_properties[$name] = $value;
        }
    }
}