<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Exception,
    ArrayAccess;

class Model implements ArrayAccess
{
    public function toArray()
    {
        $array = array();
        foreach (array_keys(get_object_vars($this)) as $name) {
            if ($name[0] == '_') {
                continue;
            }
            $array[$name] = $this->__get($name);
        }
        return $array;
    }

    public function fromArray(array $array)
    {
        foreach ($array as $name => $value) {
            $this->__set($name, $value);
        }
        return $this;
    }

    public function __set($name, $value)
    {
        if ($name[0] == '_') {
            throw new Exception("Access to '{$name}' is prohibited");  
        }
        if (method_exists($this, $name)) {
            $this->{$name}($value);
        } else if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
    }

    public function __get($name)
    {
        if ($name[0] == '_') {
            throw new Exception("Access to '{$name}' is prohibited");  
        }
        if (method_exists($this, $name)) {
            return $this->{$name}();
        } else if (property_exists($this, $name)) {
            return $this->{$name};
        } else {
            throw new Exception("Property or method '{$name}' is not defined");  
        }
    }

    public function __isset($name)
    {
        if ($name[0] == '_') {
            throw new Exception("Access to '{$name}' is prohibited");  
        }
        if (method_exists($this, $name)) {
            return $this->{$name}() !== null;
        } else if (property_exists($this, $name)) {
            return $this->{$name} !== null;
        } else {
            throw new Exception("Property or method '{$name}' is not defined");  
        }
    }

    public function __unset($name)
    {
        if ($name[0] == '_') {
            throw new Exception("Access to '{$name}' is prohibited");  
        }
        if (property_exists($this, $name)) {
            $this->{$name} = null;
        } else {
            throw new Exception("Property or method '{$name}' is not defined");  
        }
    }

    public function __call($name, array $args)
    {
        if ($name[0] == '_') {
            throw new Exception("Access to '{$name}' is prohibited");  
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