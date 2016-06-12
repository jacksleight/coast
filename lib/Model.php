<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use ArrayAccess;
use Closure;
use Coast\Model;
use Coast\Model\Metadata;

class Model implements ArrayAccess
{
    const TYPE_ONE  = 'one';
    const TYPE_MANY = 'many';

    protected static $_metadataStatic = [];
   
    protected $_metadata;

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
        return $this->_metadata = clone static::metadataStatic();
    }

    protected function _metadataModify()
    {
        return $this->_metadata;
    }

    public function metadata(Metadata $metadata = null)
    {
        if (func_num_args() > 0) {
            $this->_metadata = $metadata;
            return $this;
        }
        if (!isset($this->_metadata)) {
            $this->_metadataBuild();
            $this->_metadataModify();
        }
        return $this->_metadata;
    }

    public function traverse(Closure $func, $deep = true, array &$exclude = array())
    {
        array_push($exclude, $this);
        $output = [];
        foreach ($this->metadata->properties() as $name => $metadata) {
            $value = $this->__get($name);
            if ($deep) {
                if (in_array($metadata['type'], [self::TYPE_ONE, self::TYPE_MANY]) && in_array($value, $exclude, true)) {
                    continue;
                }
                if ($metadata['type'] == self::TYPE_ONE) {
                    if (isset($value)) {
                        $value = $value->traverse($func, $deep, $exclude);
                    }
                } else if ($metadata['type'] == self::TYPE_MANY) {
                    $items = [];
                    foreach ($value as $key => $item) {
                        $items[$key] = $item->traverse($func, $deep, $exclude);
                    }
                    $value = $items;
                }
            }
            $output[$name] = $func($name, $value, $metadata);
        }
        return $output;
    }

    public function fromArray(array $array, $deep = true)
    {
        foreach ($array as $name => $value) {
            if (!$deep) {
                $this->__set($name, $array[$name]);
                continue;
            }
            $current  = $this->__get($name);
            $metadata = $this->metadata->property($name);
            if ($metadata['type'] == self::TYPE_ONE) {
                if (isset($value)) {
                    if (!isset($current) && isset($metadata['create'])) {
                        $class = $metadata['create'];
                        $this->__set($name, $current = new $class());
                    }
                    if (isset($current)) {
                        $current->fromArray($value, $deep);
                    }
                } else {
                    $this->__unset($name);
                }
            } else if ($metadata['type'] == self::TYPE_MANY) {
                if (isset($value)) {
                    $keys = [];
                    foreach ($current as $key => $item) {
                        if (!isset($value[$key])) {
                            $keys[] = $key;
                        }
                    }
                    foreach ($keys as $key) {
                        unset($current[$key]);
                    }
                    foreach ($value as $key => $item) {
                        if (isset($item)) {
                            if (!isset($current[$key]) && isset($metadata['create'])) {
                                $class = $metadata['create'];
                                $current[$key] = new $class();
                            }
                            if (isset($current[$key])) {
                                $current[$key]->fromArray($item, $deep);
                            }
                        } else {
                            unset($current[$key]);
                        }
                    }
                } else {
                    foreach ($current as $key => $item) {
                        unset($current[$key]);
                    }
                }
            } else {
                $this->__set($name, $array[$name]);
            }
        }
        return $this;
    }

    public function toArray($deep = true)
    {
        return $this->traverse(function($name, $value, $metadata = null) {
            return $value;
        }, $deep);
    }

    public function isValid($deep = true)
    {
        $isValid = true;
        $this->traverse(function($name, $value, $metadata) use (&$isValid) {
            if (!$metadata['validator']($this->__get($name), $this)) {
                $isValid = false;
            }
        }, $deep);
        return $isValid;
    }

    public function debug($deep = true)
    {
        return $this->traverse(function($name, $value, $metadata) {
            return [
                'value'  => $value,
                'errors' => $metadata['validator']->errors(),
            ];
        }, $deep);
    }
    
    protected function _parse($name, $value)
    {
        $metadata = $this->metadata->property($name);
        $value = $metadata['filter']->filter($value);
        $value = $metadata['transformer']->transform($value);
        return $value;
    }
    
    protected function _set($name, $value)
    {
        $this->{$name} = $this->_parse($name, $value);
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
            throw new Model\Exception("Property or method '{$name}' is not defined");  
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
            throw new Model\Exception("Property or method '{$name}' is not defined");  
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
            throw new Model\Exception("Property or method '{$name}' is not defined");  
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
            throw new Model\Exception("Property or method '{$name}' is not defined");  
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