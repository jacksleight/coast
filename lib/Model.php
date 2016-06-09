<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use ArrayAccess;
use Coast\Model;
use Coast\Model\Metadata;

class Model implements ArrayAccess
{
    protected static $_metadataStatic = [];
   
    protected $_metadata;

    protected static function _metadataStatic()
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

    protected static function _metadataStaticModifier()
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
            static::_metadataStatic();
            static::_metadataStaticModifier();
        }
        return static::$_metadataStatic[$class];
    }

    protected function _metadata()
    {
        return $this->_metadata = clone static::metadataStatic();
    }

    public function metadata(Metadata $metadata = null)
    {
        if (func_num_args() > 0) {
            $this->_metadata = $metadata;
            return $this;
        }
        if (!isset($this->_metadata)) {
            $this->_metadata();
        }
        return $this->_metadata;
    }

    public function toArray($deep = true, array &$exclude = array())
    {
        array_push($exclude, $this);
        $array = [];
        foreach ($this->metadata->properties() as $name => $metadata) {
            if (!$deep) {
                $array[$name] = $this->__get($name);
                continue;
            }
            $isOne  = in_array($metadata['type'], ['one', 'oneToOne', 'manyToOne']);
            $isMany = in_array($metadata['type'], ['many', 'oneToMany', 'manyToMany']);
            $value  = $this->__get($name);
            if (($isOne || $isMany) && in_array($value, $exclude, true)) {
                continue;
            }
            if ($isMany) {
                $array[$name] = [];
                foreach ($value as $key => $item) {
                    $array[$name][$key] = $item->toArray($deep, $exclude);
                }
            } else if ($isOne) {
                if (!isset($value)) {
                    $array[$name] = null;
                    continue;
                }
                $array[$name] = $value->toArray($deep, $exclude);
            } else {
                $array[$name] = $value;
            }            
        }
        return $array;
    }

    public function fromArray(array $array, $deep = true)
    {
        foreach ($this->metadata->properties() as $name => $metadata) {
            if (!array_key_exists($name, $array)) {
                continue;
            }
            if (!$deep) {
                $this->__set($name, $array[$name]);
                continue;
            }
            $isOne  = in_array($metadata['type'], ['one', 'oneToOne', 'manyToOne']);
            $isMany = in_array($metadata['type'], ['many', 'oneToMany', 'manyToMany']);
            $value  = $this->__get($name);
            if ($isMany) {
                foreach ($array[$name] as $key => $item) {
                    if (!isset($value[$key])) {
                        $class = $metadata['class'];
                        $value[$key] = new $class();
                    }
                }
                foreach ($value as $key => $item) {
                    if (!isset($array[$name][$key])) {
                        unset($value[$key]);
                        continue;
                    }
                    $item->fromArray($array[$name][$key], $deep);
                }
            } else if ($isOne) {
                if (!isset($value)) {
                    $class = $metadata['class'];
                    $this->__set($name, $value = new $class());
                }
                if (!isset($array[$name])) {
                    $this->__unset($name);
                    continue;
                }
                $value->fromArray($array[$name], $deep);
            } else {
                $this->__set($name, $array[$name]);
            }
        }
        return $this;
    }

    public function isValid($deep = true, array &$exclude = array())
    {
        array_push($exclude, $this);
        $isValid = true;
        foreach ($this->metadata->properties() as $name => $metadata) {
            $value = $this->__get($name);
            if (!$metadata['validator']->validate($value)) {
                $isValid = false;
            }
            if (!$deep) {
                continue;
            }
            $isOne  = in_array($metadata['type'], ['one', 'oneToOne', 'manyToOne']);
            $isMany = in_array($metadata['type'], ['many', 'oneToMany', 'manyToMany']);
            if (($isOne || $isMany) && in_array($value, $exclude, true)) {
                continue;
            }
            if ($isMany) {
                foreach ($value as $key => $item) {
                    if (!$item->isValid($deep, $exclude)) {
                        $isValid = false;
                    }
                }
            } else if ($isOne) {
                if (!isset($value)) {
                    continue;
                }
                if (!$value->isValid($deep, $exclude)) {
                    $isValid = false;
                }          
            }
        }
        return $isValid;
    }

    public function errors($deep = true, array &$exclude = array())
    {
        array_push($exclude, $this);
        $errors = [];
        foreach ($this->metadata->properties() as $name => $metadata) {
            if (!$metadata['validator']->isValid()) {
                $errors[$name] = $metadata['validator']->errors();
            }
            if (!$deep) {
                continue;
            }
            $isOne  = in_array($metadata['type'], ['one', 'oneToOne', 'manyToOne']);
            $isMany = in_array($metadata['type'], ['many', 'oneToMany', 'manyToMany']);
            $value  = $this->__get($name);
            if (($isOne || $isMany) && in_array($value, $exclude, true)) {
                continue;
            }
            if ($isMany) {
                $errors[$name] = [];
                foreach ($value as $key => $item) {
                    $errors[$name][$key] = $item->errors($deep, $exclude);
                }
            } else if ($isOne) {
                if (!isset($value)) {
                    continue;
                }
                $errors[$name] = $value->errors($deep, $exclude);
            }
        }
        return $errors;
    }
    
    protected function _set($name, $value)
    {
        $metadata = $this->metadata->property($name);
        $value = $metadata['filter']->filter($value);
        return $this->{$name} = $value;
    }
    
    protected function _get($name)
    {
        return $this->{$name};
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
            return $this->_get($name) !== null;
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
            $this->_set($name, null);
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