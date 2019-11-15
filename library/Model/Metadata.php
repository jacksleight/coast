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
use Closure;

class Metadata implements JsonSerializable
{
    protected $_className;

    protected $_instance;

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

    public function instance(Model $instance = null)
    {
        if (func_num_args() > 0) {
            $this->_instance = $instance;
            return $this;
        }
        return $this->_instance;
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
                    'inverse'     => null,
                    'type'        => null,
                    'values'      => null,
                    'filter'      => new Filter(),
                    'transformer' => new Transformer(),
                    'validator'   => new Validator(),
                    'traverse'    => [],
                    'className'   => null,
                    'classArgs'   => null,
                    'isImmutable' => false,
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

    public function toArray(callable $parser = null)
    {
        $className  = $this->_className;
        $defaults   = (new \ReflectionClass($className))->getDefaultProperties();
        $properties = [];
        foreach ($this->_properties as $name => $metadata) {
            $property = [
                'default'   => $defaults[$name],
                'validator' => $metadata['validator']->toArray(),
                'errors'    => $metadata['validator']->errors(),
            ] + $metadata;
            $isTraverse = array_intersect([
                Model::TRAVERSE_SET,
                Model::TRAVERSE_VALIDATE,
                Model::TRAVERSE_META,
            ], $metadata['traverse']);
            if (!in_array($metadata['type'], [
                Model::TYPE_ONE,
                Model::TYPE_MANY,
            ])) {
                $isTraverse = false;
            }
            if (!$isTraverse) {
                $properties[$name] = $property;
                continue;
            }
            if (isset($metadata['className'])) {
                $propertyClassName = $metadata['className'];
                $property['metadataStatic'] = $propertyClassName::metadataStatic()->toArray($parser);
            }
            if (isset($this->_instance->{$name})) {
                $value = $this->_instance->{$name};
                if ($metadata['type'] == Model::TYPE_ONE) {
                    $property['metadata'] = $value->metadata()->toArray($parser);
                } else if ($metadata['type'] == Model::TYPE_MANY) {
                    $property['metadata'] = [];
                    foreach ($value as $i => $item) {
                        $property['metadata'][$i] = $item->metadata()->toArray($parser);
                    }
                }
            }
            $properties[$name] = $property;
        }
        $array = [
            'identity'   => isset($this->_instance) ? Model::modelIdentify($this->_instance) : null,
            'className'  => $className,
            'properties' => $properties,
            'others'     => $this->_others,
        ];
        if (isset($parser)) {
            $array = $parser($array);
        }
        return $array;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}