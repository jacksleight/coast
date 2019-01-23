<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use ArrayAccess;
use Closure;
use Coast;
use Coast\Model;
use Coast\Model\Metadata;
use Traversable;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

class Model implements ArrayAccess, JsonSerializable
{
    const TRAVERSE_MODE_READ     = 'read';
    const TRAVERSE_MODE_WRITE    = 'write';
    const TRAVERSE_MODE_VALIDATE = 'write';
    const TRAVERSE_OUTPUT_SKIP   = '__Coast\Model::SKIP__';

    const PROPERTY_TYPE_ONE  = 'one';
    const PROPERTY_TYPE_MANY = 'many';

    protected static $_metadataStatic = [];

    protected $_metadataSource;

    protected $_metadata;

    protected static $_initMethods = [
        'isInitialized',
        '_isInitialized',
        '__isInitialized',
    ];

    protected static $_factory;

    public static function initMethods($initMethods = null)
    {
        if (func_num_args() > 0) {
            self::$_initMethods = $initMethods;
        }
        return self::$_initMethods;
    }

    public static function factory($factory = null)
    {
        if (func_num_args() > 0) {
            self::$_factory = $factory;
        }
        return self::$_factory;
    }

    public static function createDefault()
    {
        $class = get_called_class();
        return new $class();
    }

    protected static function _metadataStaticBuild()
    {
        $class    = get_called_class();
        $metadata = new Metadata($class);
        $reflect  = new ReflectionClass($class);
        $names    = array_map(function($v) { return $v->getName(); }, array_diff(
            $reflect->getProperties(),
            $reflect->getProperties(ReflectionProperty::IS_STATIC)
        ));
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

    public function metadataReset($mode = false)
    {
        $this->traverseModels(function() {
            $this->_metadata = null;
        }, $mode);
        return $this;
    }

    public function traverse(Closure $func, $mode, array $history = array())
    {
        array_push($history, $this);
        $func = $func->bindTo($this);
        $output = [];
        foreach ($this->metadata->properties() as $name => $metadata) {
            $value = $this->__get($name);
            $isTraverse = in_array($mode, $metadata['traverseModes']);
            if (!in_array($metadata['type'], [
                self::PROPERTY_TYPE_ONE,
                self::PROPERTY_TYPE_MANY,
            ])) {
                $isTraverse = false;
            }
            if (is_object($value)) {
                foreach (self::$_initMethods as $method) {
                    if (method_exists($value, $method) && !$value->$method()) {
                        $isTraverse = false;
                        break;
                    }
                }
            }
            if (!$isTraverse) {
                $value = $func($name, $value, $metadata, $isTraverse);
                if ($value !== self::TRAVERSE_OUTPUT_SKIP) {
                    $output[$name] = $value;
                }
                continue;
            }
            if (in_array($metadata['type'], [
                self::PROPERTY_TYPE_ONE,
                self::PROPERTY_TYPE_MANY,
            ]) && in_array($value, $history, true)) {
                continue;
            }
            if ($metadata['type'] == self::PROPERTY_TYPE_ONE) {
                if (isset($value)) {
                    $value = $value->traverse($func, $mode, $history);
                }
            } else if ($metadata['type'] == self::PROPERTY_TYPE_MANY) {
                $items = [];
                foreach ($value as $key => $item) {
                    $items[$key] = $item->traverse($func, $mode, $history);
                }
                $value = $items;
            }
            $value = $func($name, $value, $metadata, $isTraverse);
            if ($value !== self::TRAVERSE_OUTPUT_SKIP) {
                $output[$name] = $value;
            }
        }
        return $output;
    }

    public function traverseModels(Closure $func, $mode, array $history = array())
    {
        array_push($history, $this);
        $func = $func->bindTo($this);
        foreach ($this->metadata->properties() as $name => $metadata) {
            if (!in_array($metadata['type'], [
                self::PROPERTY_TYPE_ONE,
                self::PROPERTY_TYPE_MANY,
            ])) {
                continue;
            }
            $value = $this->__get($name);
            $isTraverse = in_array($mode, $metadata['traverseModes']);
            if (is_object($value)) {
                foreach (self::$_initMethods as $method) {
                    if (method_exists($value, $method) && !$value->$method()) {
                        $isTraverse = false;
                        break;
                    }
                }
            }
            if (!$isTraverse) {
                continue;
            }
            if (in_array($value, $history, true)) {
                continue;
            }
            if ($metadata['type'] == self::PROPERTY_TYPE_ONE) {
                if (isset($value)) {
                    $value->traverseModels($func, $mode, $history);
                }
            } else if ($metadata['type'] == self::PROPERTY_TYPE_MANY) {
                foreach ($value as $item) {
                    $item->traverseModels($func, $mode, $history);
                }
            }
        }
        $func();
    }

    public function fromArray(array $array)
    {
        $mode = self::TRAVERSE_MODE_WRITE;
        foreach ($array as $name => $value) {
            $metadata = $this->metadata->property($name);
            if (!isset($metadata)) {
                $this->__setUnknown($name, $value);
                continue;
            }
            $isTraverse = in_array($mode, $metadata['traverseModes']);
            if (!in_array($metadata['type'], [
                self::PROPERTY_TYPE_ONE,
                self::PROPERTY_TYPE_MANY,
            ])) {
                $isTraverse = false;
            }
            if (is_object($value)) {
                foreach (self::$_initMethods as $method) {
                    if (method_exists($value, $method) && !$value->$method()) {
                        $isTraverse = false;
                        break;
                    }
                }
            }
            if (!$isTraverse) {
                $this->__set($name, $value);
                continue;
            }
            if ($metadata['type'] == self::PROPERTY_TYPE_ONE) {
                $current = $this->__get($name);
                if (!isset($value)) {
                    $this->__unset($name);
                    continue;
                }
                if (!isset($current) && $metadata['isConstructable']) {
                    $this->__set($name, $current = $this->_constructModel($metadata['className'], $metadata['classArgs']));
                }
                if (isset($current)) {
                    if ($metadata['isImmutable']) {
                        $current = clone $current;
                        $this->__set($name, $current);
                    }
                    $current->fromArray($value, $mode);
                }
            } else if ($metadata['type'] == self::PROPERTY_TYPE_MANY) {
                $current = $this->__get($name);
                if (!is_array($current) && (!$current instanceof Traversable && !$current instanceof ArrayAccess)) {
                    throw new Model\Exception("Value of MANY property '" . get_class($this) . "->{$name}' must be an array or object that implements Traversable and ArrayAccess");
                }
                if ($metadata['isImmutable']) {
                    $current = clone $current;
                    $this->__set($name, $current);
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
                    if (!isset($current[$key]) && $metadata['isConstructable']) {
                        $current[$key] = $this->_constructModel($metadata['className'], $metadata['classArgs']);
                    }
                    if (isset($current[$key])) {
                        if ($metadata['isImmutable']) {
                            $current[$key] = clone $current[$key];
                        }
                        $current[$key]->fromArray($item, $mode);
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

    public function toArray()
    {
        return $this->traverse(function($name, $value, $metadata = null) {
            return $value;
        }, self::TRAVERSE_MODE_READ);
    }

    public function isValid()
    {
        $isValid = true;
        $this->traverse(function($name, $value, $metadata) use (&$isValid) {
            if (!$metadata['validator']($this->__get($name), $this)) {
                $isValid = false;
            }
        }, self::TRAVERSE_MODE_VALIDATE);
        return $isValid;
    }

    protected function _constructModel($className, $classArgs = null)
    {
        return (new \ReflectionClass($className))->newInstanceArgs(isset($classArgs) ? $classArgs : []);
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

    public function jsonSerialize()
    {
        return $this->toArray(false);
    }
}