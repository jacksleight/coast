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
    const TRAVERSE_SET      = 'set';
    const TRAVERSE_GET      = 'get';
    const TRAVERSE_CREATE   = 'create';
    const TRAVERSE_DELETE   = 'delete';
    const TRAVERSE_VALIDATE = 'validate';
    const TRAVERSE_META     = 'meta';
    
    const SKIP      = '__Coast\Model::SKIP__';
    const UNDEFINED = '__Coast\Model::UNDEFINED__';

    const TYPE_ONE  = 'one';
    const TYPE_MANY = 'many';

    protected static $_metadataStatic = [];

    protected $_metadataSource;

    protected $_metadata;

    protected static $_modelIdentifier;

    protected static $_modelCreator;

    protected static $_modelFetcher;

    protected static $_modelDeleter;

    protected static $_modelFinder;

    protected static $_modelTraverseChecker;

    protected static $_modelVerifier;

    protected static $_modelDeleteChecker;

    public static function modelIdentifier($modelIdentifier = null)
    {
        if (func_num_args() > 0) {
            self::$_modelIdentifier = $modelIdentifier;
        }
        return self::$_modelIdentifier;
    }

    public static function modelCreator($modelCreator = null)
    {
        if (func_num_args() > 0) {
            self::$_modelCreator = $modelCreator;
        }
        return self::$_modelCreator;
    }

    public static function modelFetcher($modelFetcher = null)
    {
        if (func_num_args() > 0) {
            self::$_modelFetcher = $modelFetcher;
        }
        return self::$_modelFetcher;
    }

    public static function modelDeleter($modelDeleter = null)
    {
        if (func_num_args() > 0) {
            self::$_modelDeleter = $modelDeleter;
        }
        return self::$_modelDeleter;
    }

    public static function modelFinder($modelFinder = null)
    {
        if (func_num_args() > 0) {
            self::$_modelFinder = $modelFinder;
        }
        return self::$_modelFinder;
    }

    public static function modelTraverseChecker($modelTraverseChecker = null)
    {
        if (func_num_args() > 0) {
            self::$_modelTraverseChecker = $modelTraverseChecker;
        }
        return self::$_modelTraverseChecker;
    }

    public static function modelVerifier($modelVerifier = null)
    {
        if (func_num_args() > 0) {
            self::$_modelVerifier = $modelVerifier;
        }
        return self::$_modelVerifier;
    }

    public static function modelDeleteChecker($modelDeleteChecker = null)
    {
        if (func_num_args() > 0) {
            self::$_modelDeleteChecker = $modelDeleteChecker;
        }
        return self::$_modelDeleteChecker;
    }

    // Returns the identity of the given $object
    public static function modelIdentify($object)
    {
        $func = self::$_modelIdentifier;
        if (isset($func)) {
            return $func($object);;
        } else {
            return null;
        }
    }

    // Returns a new object of the given $className with $classArgs
    public static function modelCreate($className, $classArgs, $array)
    {
        $func = self::$_modelCreator;
        if (isset($func)) {
            return $func($className, $classArgs, $array);
        } else {
            return (new \ReflectionClass($className))->newInstanceArgs(isset($classArgs) ? $classArgs : []);
        }
    }

    // Returns an existing object of the given $className and $id
    public static function modelFetch($className, $id)
    {
        $func = self::$_modelFetcher;
        if (isset($func)) {
            return $func($className, $id);;
        } else {
            return null;
        }
    }

    // Deletes an existing $object
    public static function modelDelete($object, $coll, $key)
    {
        $func = self::$_modelDeleter;
        if (isset($func)) {
            $func($object, $coll, $key);
        } else {
            unset($coll[$key]);
        }
    }

    // Returns the key of item in $coll that macthes the $key/$item, must return false on no match
    public static function modelFind($object, $coll, $key, $item, $isCurrent)
    {
        $func = self::$_modelFinder;
        if (isset($func)) {
            return $func($object, $coll, $key, $item, $isCurrent);;
        } else {
            return isset($coll[$key]) ? $key : false;
        }
    }

    // Returns boolean whether the $object is valid for deep traversal
    public static function modelTraverseCheck($object)
    {
        $func = self::$_modelTraverseChecker;
        if (isset($func)) {
            return $func($object);;
        } else {
            return true;
        }
    }

    // Returns boolean whether the $array is applicable to the matched $object
    public static function modelVerify($object, $array)
    {
        $func = self::$_modelVerifier;
        if (isset($func)) {
            $func($object, $array);;
        }
    }

    // Returns boolean whether the value of $item means the associated $currentItem should be deleted
    public static function modelDeleteCheck($object, $item)
    {
        $func = self::$_modelDeleteChecker;
        if (isset($func)) {
            return $func($object, $item);;
        } else {
            return $item == null || $item == self::UNDEFINED;
        }
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
        $this->_metadataSource = clone static::metadataStatic();
        $this->_metadataSource->instance($this);
        return $this->_metadataSource;
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

    public function metadataReset($traverse = false)
    {
        $this->traverseModels(function() {
            $this->_metadata = null;
        }, $traverse);
        return $this;
    }

    public function traverse(callable $parser, $traverse, array $history = array())
    {
        array_push($history, $this);
        if ($parser instanceof Closure) {
            $parser = $parser->bindTo($this);
        }
        $output = [];
        foreach ($this->metadata->properties() as $name => $metadata) {
            $value = $this->__get($name);
            if (in_array($metadata['type'], [
                self::TYPE_ONE,
                self::TYPE_MANY,
            ]) && in_array($value, $history, true)) {
                continue;
            }
            $isTraverse = in_array($traverse, $metadata['traverse']);
            if (!in_array($metadata['type'], [
                self::TYPE_ONE,
                self::TYPE_MANY,
            ])) {
                $isTraverse = false;
            }
            if (is_object($value) && !self::modelTraverseCheck($value)) {
                $isTraverse = false;
            }
            if (!$isTraverse) {
                $value = $parser($name, $value, $metadata, $isTraverse);
                if ($value !== self::SKIP) {
                    $output[$name] = $value;
                }
                continue;
            }
            if ($metadata['type'] == self::TYPE_ONE) {
                if (isset($value)) {
                    $value = $value->traverse($parser, $traverse, $history);
                }
            } else if ($metadata['type'] == self::TYPE_MANY) {
                $items = [];
                foreach ($value as $key => $item) {
                    $items[$key] = $item->traverse($parser, $traverse, $history);
                }
                $value = $items;
            }
            $value = $parser($name, $value, $metadata, $isTraverse);
            if ($value !== self::SKIP) {
                $output[$name] = $value;
            }
        }
        return $output;
    }

    public function traverseModels(callable $parser, $traverse, array $history = array())
    {
        array_push($history, $this);
        if ($parser instanceof Closure) {
            $parser = $parser->bindTo($this);
        }
        foreach ($this->metadata->properties() as $name => $metadata) {
            if (!in_array($metadata['type'], [
                self::TYPE_ONE,
                self::TYPE_MANY,
            ])) {
                continue;
            }
            $value = $this->__get($name);
            if (in_array($value, $history, true)) {
                continue;
            }
            if (is_object($value) && !self::modelTraverseCheck($value)) {
                continue;
            }
            $isTraverse = in_array($traverse, $metadata['traverse']);
            if (!$isTraverse) {
                continue;
            }
            if ($metadata['type'] == self::TYPE_ONE) {
                if (isset($value)) {
                    $value->traverseModels($parser, $traverse, $history);
                }
            } else if ($metadata['type'] == self::TYPE_MANY) {
                foreach ($value as $item) {
                    $item->traverseModels($parser, $traverse, $history);
                }
            }
        }
        $parser();
    }

    public function fromArray(array $array)
    {
        self::modelVerify($this, $array);
        $traverse = self::TRAVERSE_SET;
        foreach ($array as $name => $value) {
            $metadata = $this->metadata->property($name);
            if (!isset($metadata)) {
                $this->__setUnknown($name, $value);
                continue;
            }
            $isTraverse = in_array($traverse, $metadata['traverse']);
            if (!in_array($metadata['type'], [
                self::TYPE_ONE,
                self::TYPE_MANY,
            ])) {
                $isTraverse = false;
            }
            if (is_object($value) && !self::modelTraverseCheck($value)) {
                $isTraverse = false;
            }
            if (!$isTraverse) {
                $this->__set($name, $value);
                continue;
            }
            if ($metadata['type'] == self::TYPE_ONE) {
                $current = $this->__get($name);
                if (in_array(self::TRAVERSE_DELETE, $metadata['traverse']) && self::modelDeleteCheck($this, $value)) {
                    self::modelDelete($this, $this, $name);
                    continue;
                }
                if (!isset($current)) {
                    if (in_array(self::TRAVERSE_CREATE, $metadata['traverse'])) {
                        $create = self::modelCreate($metadata['className'], $metadata['classArgs'], $value);
                        $create->fromArray($value);
                        $this->__set($name, $create);
                        if (isset($metadata['inverse'])) {
                            $create[$metadata['inverse']] = $this;
                        }
                    }
                } else {
                    if ($metadata['isImmutable']) {
                        $current = clone $current;
                        $this->__set($name, $current);
                    }
                    $current->fromArray($value);
                }
            } else if ($metadata['type'] == self::TYPE_MANY) {
                $current = $this->__get($name);
                if (!is_array($current) && (!$current instanceof Traversable && !$current instanceof ArrayAccess)) {
                    throw new Model\Exception("Value of MANY property '" . get_class($this) . "->{$name}' must be an array or object that implements Traversable and ArrayAccess");
                }
                if ($metadata['isImmutable']) {
                    $current = clone $current;
                    $this->__set($name, $current);
                }
                // Check incoming data against existing and update/create
                foreach ($value as $key => $item) {
                    if (self::modelDeleteCheck($this, $item)) {
                        continue;
                    }
                    $currentKey = self::modelFind($this, $current, $key, $item, true);
                    if ($currentKey === false) {
                        if (in_array(self::TRAVERSE_CREATE, $metadata['traverse'])) {
                            $create = self::modelCreate($metadata['className'], $metadata['classArgs'], $item);
                            $create->fromArray($item);
                            $current[] = $create;
                            if (isset($metadata['inverse'])) {
                                $create[$metadata['inverse']] = $this;
                            }
                        }
                    } else {
                        if ($metadata['isImmutable']) {
                            $current[$currentKey] = clone $current[$currentKey];
                        }
                        $current[$currentKey]->fromArray($item);
                    }
                }
                // Check existing data against incoming and remove 
                foreach ($current as $currentKey => $currentItem) {
                    $key  = self::modelFind($this, $value, $currentKey, $currentItem, false);
                    $item = $key === false
                        ? self::UNDEFINED
                        : $value[$key];
                    if (in_array(self::TRAVERSE_DELETE, $metadata['traverse']) && self::modelDeleteCheck($this, $item)) {
                        self::modelDelete($this, $current, $currentKey);
                    }
                }
                // Rebase current keys
                // $current may be a persisted object so we can't replace
                // it and we can't use any functions that expect an array
                $currentItems = [];
                foreach ($current as $currentKey => $currentItem) {
                    $currentItems[] = $currentItem;
                    unset($current[$currentKey]);
                }
                $i = 0;
                foreach ($currentItems as $currentItem) {
                    $current[$i] = $currentItem;
                    $i++;
                }
                $this->__set($name, $current);
            } else {
                $this->__set($name, $value);
            }
        }
        return $this;
    }

    public function toArray(callable $parser = null)
    {
        if (!isset($parser)) {
            $parser = function($name, $value, $metadata, $isTraverse) {
                return $value;
            };
        }
        return $this->traverse($parser, self::TRAVERSE_GET);
    }

    public function isValid($context = null)
    {
        $isValid = true;
        $validators = $this->metadata()->validators();
        foreach ($validators as $validator) {
            if (!$validator($this, $context)) {
                $isValid = false;
            }
        }
        $this->traverse(function($name, $value, $metadata, $isTraverse) use (&$isValid) {
            if (!$metadata['validator']($this->__get($name), $this)) {
                $isValid = false;
            }
        }, self::TRAVERSE_VALIDATE);
        return $isValid;
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

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
       return $this->__set($offset, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}