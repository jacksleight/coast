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
use Coast\Transformer;

class Metadata 
{
    protected $_className;

    protected $_properties = [];

    protected $_extras = [];

    public function __construct($className)
    {
        $this->_className = $className;
    }

    public function className()
    {
        return $this->_className;
    }

    public function property($name, array $value = null)
    {
        if (!property_exists($this->_className, $name)) {
            return null;
        }
        if (func_num_args() > 1) {
            if (!isset($this->_properties[$name])) {
                $this->_properties[$name] = [
                    'name'        => $name,
                    'type'        => null,
                    'create'      => null,
                    'filter'      => new Filter(),
                    'transformer' => new Transformer(),
                    'validator'   => new Validator(),
                ];
            }
            $current = $this->_properties[$name];
            if (isset($value['filter']) && is_array($value['filter'])) {
                call_user_func_array([$current['filter'], 'steps'], $value['filter']);
                unset($value['filter']);
            }
            if (isset($value['transformer']) && is_array($value['transformer'])) {
                call_user_func_array([$current['transformer'], 'steps'], $value['transformer']);
                unset($value['transformer']);
            }
            if (isset($value['validator']) && is_array($value['validator'])) {
                call_user_func_array([$current['validator'], 'steps'], $value['validator']);
                unset($value['validator']);
            }
            $this->_properties[$name] = $value + $current;
            return $this;
        }
        return $this->_properties[$name];
    }

    public function properties(array $properties = null)
    {
        if (func_num_args() > 0) {
            if (Coast\is_array_assoc($properties)) {
                foreach ($properties as $name => $value) {
                    $this->property($name, $value);
                }
                return $this;
            } else {
                return array_intersect_key($this->_properties, array_flip($properties));
            }
        }
        return $this->_properties;
    }

    public function __clone()
    {
        foreach ($this->_properties as $name => $value) {
            if (isset($value['filter'])) {
                $value['filter'] = clone $value['filter'];
            }
            if (isset($value['transformer'])) {
                $value['transformer'] = clone $value['transformer'];
            }
            if (isset($value['validator'])) {
                $value['validator'] = clone $value['validator'];
            }
            $this->_properties[$name] = $value;
        }
    }

    public function extra($name, $value = null)
    {
        if (func_num_args() > 1) {
            if (isset($value)) {
                $this->_extras[$name] = $value;
            } else {
                unset($this->_extras[$name]);
            }
            return $this;
        }
        return isset($this->_extras[$name])
            ? $this->_extras[$name]
            : null;
    }

    public function extras(array $extras = null)
    {
        if (func_num_args() > 0) {
            foreach ($extras as $name => $value) {
                $this->extra($name, $value);
            }
            return $this;
        }
        return $this->_extras;
    }

    public function __set($name, $value)
    {
        return $this->extra($name, $value);
    }

    public function __get($name)
    {
        return $this->extra($name);
    }

    public function __isset($name)
    {
        return $this->extra($name) !== null;
    }

    public function __unset($name)
    {
        return $this->extra($name, null);
    }
}