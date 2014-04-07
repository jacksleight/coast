<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Model
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

    public function __get($name)
    {
        if ($name[0] == '_') {
            throw new Exception("Access to '{$name}' is prohibited");  
        }
        if (method_exists($this, $name)) {
            return $this->{$name}();
        } else {
            return $this->{$name};
        }
    }

    public function __set($name, $value)
    {
        if ($name[0] == '_') {
            throw new Exception("Access to '{$name}' is prohibited");  
        }
        if (method_exists($this, $name)) {
            $this->{$name}($value);
        } else {
            $this->{$name} = $value;
        }
    }

    public function __isset($name)
    {
        if ($name[0] == '_') {
            throw new Exception("Access to '{$name}' is prohibited");  
        }
        return isset($this->{$name});
    }

    public function __unset($name)
    {
        if ($name[0] == '_') {
            throw new Exception("Access to '{$name}' is prohibited");  
        }
        unset($this->{$name});
    }

    public function __call($name, array $args)
    {
        if ($name[0] == '_') {
            throw new Exception("Access to '{$name}' is prohibited");  
        }
        if (isset($args[0])) {
            $this->{$name} = $args[0];
            return $this;
        }
        return $this->{$name};
    }
}