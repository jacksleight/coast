<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use ArrayAccess;
use Iterable;
use Closure;
use Coast;
use Coast\Model;
use Coast\Model\Metadata;

class Model implements ArrayAccess
{
    const TYPE_ONE  = 'one';
    const TYPE_MANY = 'many';

    protected static $_metadataStatic = [];
   
    protected $_metadataSource;

    protected $_metadata;

    protected static $_initMethods = [
        'isInitialized',
        '_isInitialized',
        '__isInitialized',
    ];

    public static function initMethods($initMethods = null)
    {
        if (func_num_args() > 0) {
            self::$_initMethods = $initMethods;
        }
        return self::$_initMethods;
    }

    protected static function _metadataStaticBuild()
    {
        $class    = get_called_class();
        $metadata = new Metadata($class);
        $names    = array_keys(get_class_vars($class));
        foreach ($names as $name) {
            if ($name[0] == '_') {
                continue;
            }
            $metadata->property($name, [
                'name' => $name,
            ]);
        }
        return static::$_metadataStatic[$class] = $metadata;
    }

    protected static function _metadataStaticModify()
    {
        $class = get_called_class();
        return static::$_metadataStatic[$class];
    }

    public static function metadataStatic(Metadata $metadata = null)
    {
        $class = get_called_class();
        if (func_num_args() > 0) {
            static::$_metadataStatic[$class] = $metadata;
            return $this;
        }
        if (!isset(static::$_metadataStatic[$class])) {
            static::_metadataStaticBuild();
            static::_metadataStaticModify();
        }
        return static::$_metadataStatic[$class];
    }

    protected function _metadataBuild()
    {
        return $this->_metadataSource = clone static::metadataStatic();
    }

    protected function _metadataModify()
    {
        return $this->_metadata = clone $this->_metadataSource;
    }

    public function metadata(Metadata $metadata = null)
    {
        if (func_num_args() > 0) {
            $this->_metadataSource = $metadata;
            $this->_metadata       = null;
            return $this;
        }
        if (!isset($this->_metadataSource)) {
            $this->_metadataBuild();
        }
        if (!isset($this->_metadata)) {
            $this->_metadataModify();
        }
        return $this->_metadata;
    }

    public function metadataReset($isTraverse = null)
    {
        $this->traverseModels(function() {
            $this->_metadata = null;
        }, $isTraverse);
        return $this;
    }

    public function traverse(Closure $func, $isTraverse = null, array &$history = array())
    {
        array_push($history, $this);
        $func = $func->bindTo($this);
        $output = [];
        foreach ($this->metadata->properties() as $name => $metadata) {
            $value = $this->__get($name);
            $isDeep = isset($isTraverse)
                ? $isTraverse
                : $metadata['isTraverse'];
            if (is_object($value)) {
                foreach (self::$_initMethods as $method) {
                    if (method_exists($value, $method) && !$value->$method()) {
                        $isDeep = false;
                        break;
                    }
                }
            }
            if (!$isDeep) {
                $output[$name] = $func($name, $value, $metadata);
                continue;
            }
            if (in_array($metadata['type'], [
                self::TYPE_ONE,
                self::TYPE_MANY,
            ]) && in_array($value, $history, true)) {
                continue;
            }
            if ($metadata['type'] == self::TYPE_ONE) {
                if (isset($value)) {
                    $value = $value->traverse($func, $isTraverse, $history);
                }
            } else if ($metadata['type'] == self::TYPE_MANY) {
                $items = [];
                foreach ($value as $key => $item) {
                    $items[$key] = $item->traverse($func, $isTraverse, $history);
                }
                $value = $items;
            }
            $output[$name] = $func($name, $value, $metadata);
        }
        return $output;
    }

    public function traverseModels(Closure $func, $isTraverse = null, array &$history = array())
    {
        array_push($history, $this);
        $func = $func->bindTo($this);
        foreach ($this->metadata->properties() as $name => $metadata) {
            if (!in_array($metadata['type'], [
                self::TYPE_ONE,
                self::TYPE_MANY,
            ])) {
                continue;
            }
            $value = $this->__get($name);
            $isDeep = isset($isTraverse)
                ? $isTraverse
                : $metadata['isTraverse'];
            if (is_object($value)) {
                foreach (self::$_initMethods as $method) {
                    if (method_exists($value, $method) && !$value->$method()) {
                        $isDeep = false;
                        break;
                    }
                }
            }
            if (!$isDeep) {
                continue;
            }
            if (in_array($value, $history, true)) {
                continue;
            }
            if ($metadata['type'] == self::TYPE_ONE) {
                if (isset($value)) {
                    $value->traverseModels($func, $isTraverse, $history);
                }
            } else if ($metadata['type'] == self::TYPE_MANY) {
                foreach ($value as $item) {
                    $item->traverseModels($func, $isTraverse, $history);
                }
            }
        }
        $func();
    }

    public function fromArray(array $array, $isTraverse = null)
    {
        foreach ($array as $name => $value) {
            $metadata = $this->metadata->property($name);
            if (!isset($metadata)) {
                $this->__setUnknown($name, $value);
                continue;
            }
            $isDeep = isset($isTraverse)
                ? $isTraverse
                : $metadata['isTraverse'];
            if (is_object($value)) {
                foreach (self::$_initMethods as $method) {
                    if (method_exists($value, $method) && !$value->$method()) {
                        $isDeep = false;
                        break;
                    }
                }
            }
            if (!$isDeep) {
                $this->__set($name, $value);
                continue;
            }
            if ($metadata['type'] == self::TYPE_ONE) {
                $current = $this->__get($name);
                if (!isset($value)) {
                    $this->__unset($name);
                    continue;
                }
                if (!isset($current) && $metadata['isConstruct']) {
                    $this->__set($name, $current = $this->_construct($metadata['construct']));
                }
                if (isset($current)) {
                    $current->fromArray($value, $isTraverse);
                }
            } else if ($metadata['type'] == self::TYPE_MANY) {
                $current = $this->__get($name);
                if (!is_array($current) && (!$current instanceof Iterable && !$current instanceof ArrayAccess)) {
                    throw new Model\Exception("Value of MANY property '" . get_class($this) . "->{$name}' must be an array or object that implements Iterable and ArrayAccess");
                }
                if (!isset($value)) {
                    foreach ($current as $key => $item) {
                        unset($current[$key]);
                    }
                    continue;
                }
                foreach ($value as $key => $item) {
                    if (!isset($item)) {
                        unset($current[$key]);
                        continue;
                    }
                    if (!isset($current[$key]) && $metadata['isConstruct']) {
                        $current[$key] = $this->_construct($metadata['construct']);
                    }
                    if (isset($current[$key])) {
                        $current[$key]->fromArray($item, $isTraverse);
                    }
                }
                $keys = [];
                foreach ($current as $key => $item) {
                    if (!isset($value[$key])) {
                        $keys[] = $key;
                    }
                }
                foreach ($keys as $key) {
                    unset($current[$key]);
                }
                $this->__set($name, $current);
            } else {
                $this->__set($name, $value);
            }
        }
        return $this;
    }

    public function toArray($isTraverse = null)
    {
        return $this->traverse(function($name, $value, $metadata = null) {
            return $value;
        }, $isTraverse);
    }

    public function isValid($isTraverse = null)
    {
        $isValid = true;
        $this->traverse(function($name, $value, $metadata) use (&$isValid) {
            if (!$metadata['validator']($this->__get($name), $this)) {
                $isValid = false;
            }
        }, $isTraverse);
        return $isValid;
    }

    public function debug($isTraverse = null)
    {
        return $this->traverse(function($name, $value, $metadata) {
            return [
                'value'  => $value,
                'errors' => $metadata['validator']->errors(),
            ];
        }, $isTraverse);
    }
    
    protected function _construct($construct)
    {
        $construct = (array) $construct;
        $className = array_shift($construct);
        $reflection = new \ReflectionClass($className);
        return $reflection->newInstanceArgs($construct);
    }
        
    protected function _set($name, $value)
    {
        $metadata = $this->metadata->property($name);
        $value = $metadata['filter']->filter($value);
        $value = $metadata['transformer']->transform($value, $this);
        $this->{$name} = $value;
    }
    
    protected function _get($name)
    {
        return $this->{$name};
    }
    
    protected function _isset($name)
    {
        return isset($this->{$name});
    }
    
    protected function _unset($name)
    {
        $this->{$name} = null;
    }

    public function __set($name, $value)
    {
        if ($name[0] == '_') {
            throw new Model\Exception("Access to '{$name}' is prohibited");  
        }
        if (method_exists($this, $name)) {
            $this->{$name}($value);
        } else if (property_exists($this, $name)) {
            $this->_set($name, $value);
        } else {
            throw new Model\Exception\NotDefined("Property or method '{$name}' is not defined");  
        }
    }

    public function __setUnknown($name, $value)
    {
        try {
            $this->__set($name, $value);
        } catch (Model\Exception\NotDefined $e) {
            try {
                $this->__set(Coast\str_camel($name), $value);
            } catch (Model\Exception\NotDefined $e) {}
        }
    }

    public function __get($name)
    {
        if ($name[0] == '_') {
            throw new Model\Exception("Access to '{$name}' is prohibited");  
        }
        if (method_exists($this, $name)) {
            return $this->{$name}();
        } else if (property_exists($this, $name)) {
            return $this->_get($name);
        } else {
            throw new Model\Exception\NotDefined("Property or method '{$name}' is not defined");  
        }
    }

    public function __isset($name)
    {
        if ($name[0] == '_') {
            throw new Model\Exception("Access to '{$name}' is prohibited");  
        }
        if (method_exists($this, $name)) {
            return $this->{$name}() !== null;
        } else if (property_exists($this, $name)) {
            return $this->_isset($name);
        } else {
            throw new Model\Exception\NotDefined("Property or method '{$name}' is not defined");  
        }
    }

    public function __unset($name)
    {
        if ($name[0] == '_') {
            throw new Model\Exception("Access to '{$name}' is prohibited");  
        }
        if (property_exists($this, $name)) {
            $this->_unset($name);
        } else {
            throw new Model\Exception\NotDefined("Property or method '{$name}' is not defined");  
        }
    }

    public function __call($name, array $args)
    {
        if ($name[0] == '_') {
            throw new Model\Exception("Access to '{$name}' is prohibited");  
        }
        if (isset($args[0])) {
            $this->__set($name, $args[0]);
            return $this;
        }
        return $this->__get($name);
    }

    public function offsetSet($offset, $value)
    {
       return $this->__set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }
}