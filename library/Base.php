<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast;
use ArrayAccess;

abstract class Base implements ArrayAccess
{
    public function __construct(array $array = [])
    {
        $this->fromArray($array);
    }

    public function fromArray(array $array = [])
    {
        foreach ($array as $name => $value) {
            $this->__set($name, $value);
        }
        return $this;
    }

    public function __set($name, $value)
    {
        if (method_exists($this, $name)) {
            if (method_is_public($this, $name)) {
                return $this->{$name}($value);
            }
            trigger_error("Cannot access protected property method " . get_class($this) . "::\${$name}()", E_USER_ERROR);
        } else if (property_exists($this, $name) && !property_is_public($this, $name)) {
            trigger_error("Cannot access protected property " . get_class($this) . "::\${$name}", E_USER_ERROR);
        } else {
            $this->{$name} = $value;
        }
    }

    public function __get($name)
    {
        if (method_exists($this, $name)) {
            if (method_is_public($this, $name)) {
                return $this->{$name}();
            }
            trigger_error("Cannot access protected property method " . get_class($this) . "::\${$name}()", E_USER_ERROR);
        } else {
            trigger_error("Undefined property " . get_class($this) . "::\${$name}", E_USER_NOTICE);
        }
    }

    public function __isset($name)
    {
        if (method_exists($this, $name)) {
            if (method_is_public($this, $name)) {
                return $this->{$name}() !== null;
            }
            trigger_error("Cannot access protected property method " . get_class($this) . "::\${$name}()", E_USER_ERROR);
        } else {
            trigger_error("Undefined property " . get_class($this) . "::\${$name}", E_USER_NOTICE);
        }
    }

    public function __unset($name)
    {
        if (method_exists($this, $name)) {
            if (method_is_public($this, $name)) {
                return $this->{$name}(null);
            }
            trigger_error("Cannot access protected property method " . get_class($this) . "::\${$name}()", E_USER_ERROR);
        } else if (property_exists($this, $name) && !property_is_public($this, $name)) {
            trigger_error("Cannot access protected property " . get_class($this) . "::\${$name}", E_USER_ERROR);
        } else {
            unset($this->{$name});
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
       return $this->__set($offset, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->__get($offset);
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
}