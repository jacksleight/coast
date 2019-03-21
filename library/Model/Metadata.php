<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Model;

use Exception;
use Coast;
use Coast\Model;
use Coast\Filter;
use Coast\Validator;
use Coast\Transformer;
use JsonSerializable;

class Metadata implements JsonSerializable
{
    protected $_className;

    protected $_properties = [];

    protected $_others = [];

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
                    'name'            => $name,
                    'inverse'         => null,
                    'type'            => null,
                    'filter'          => new Filter(),
                    'transformer'     => new Transformer(),
                    'validator'       => new Validator(),
                    'className'       => null,
                    'classArgs'       => null,
                    'traverse'        => [],
                    'isConstructable' => false,
                    'isImmutable'     => false,
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

    public function other($name, $value = null)
    {
        if (func_num_args() > 1) {
            if (isset($value)) {
                $this->_others[$name] = $value;
            } else {
                unset($this->_others[$name]);
            }
            return $this;
        }
        return isset($this->_others[$name])
            ? $this->_others[$name]
            : null;
    }

    public function others(array $others = null)
    {
        if (func_num_args() > 0) {
            foreach ($others as $name => $value) {
                $this->other($name, $value);
            }
            return $this;
        }
        return $this->_others;
    }

    public function __set($name, $value)
    {
        return $this->other($name, $value);
    }

    public function __get($name)
    {
        return $this->other($name);
    }

    public function __isset($name)
    {
        return $this->other($name) !== null;
    }

    public function __unset($name)
    {
        return $this->other($name, null);
    }

    public function jsonSerialize()
    {
        $className  = $this->_className;
        $properties = $this->_properties;
        $default    = new $className();
        foreach ($properties as $name => $metadata) {
            $properties[$name] += [
                'default' => $default->{$name},
            ];
            if (!in_array($metadata['type'], [
                Model::TYPE_ONE,
                Model::TYPE_MANY,
            ]) || !in_array(Model::TRAVERSE_SET, $metadata['traverse']) || !isset($metadata['className'])) {
                continue;
            }
            $propertyClassName = $metadata['className'];
            $properties[$name] += [
                'metadata' => $propertyClassName::metadataStatic(),
            ];
        }
        return [
            'className'  => $className,
            'properties' => $properties,
            'others'     => $this->_others,
        ];
    }
}