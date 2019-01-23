<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast;
use ArrayAccess;

abstract class BasePublic implements ArrayAccess
{
    public function __construct(array $props = [])
    {
        foreach ($props as $name => $value) {
            $this->__set($name, $value);
        }
    }

    public function __set($name, $value)
    {
        if (method_exists($this, $name)) {
            if (method_is_public($this, $name)) {
                return $this->{$name}($value);
            }
            trigger_error("Cannot access protected property method " . get_class($this) . "::\${$name}()", E_USER_ERROR);
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
        } else if (!property_exists($this, $name)) {
            trigger_error("Undefined property " . get_class($this) . "::\${$name}", E_USER_NOTICE);
        } else {
            return $this->{$name};
        }
    }

    public function __isset($name)
    {
        if (method_exists($this, $name)) {
            if (method_is_public($this, $name)) {
                return $this->{$name}() !== null;
            }
            trigger_error("Cannot access protected property method " . get_class($this) . "::\${$name}()", E_USER_ERROR);
        } else if (!property_exists($this, $name)) {
            trigger_error("Undefined property " . get_class($this) . "::\${$name}", E_USER_NOTICE);
        } else {
            return isset($this->{$name});
        }
    }

    public function __unset($name)
    {
        if (method_exists($this, $name)) {
            if (method_is_public($this, $name)) {
                return $this->{$name}(null);
            }
            trigger_error("Cannot access protected property method " . get_class($this) . "::\${$name}()", E_USER_ERROR);
        } else {
            unset($this->{$name});
        }
    }

    public function __call($name, array $args)
    {
        if (method_exists($this, $name)) {
            trigger_error("Cannot access protected method " . get_class($this) . "::\${$name}()", E_USER_ERROR);
        } else if (!property_exists($this, $name)) {
            trigger_error("Undefined property " . get_class($this) . "::\${$name}", E_USER_NOTICE);
        } else {
            if (isset($args[0])) {
                $this->{$name} = $args[0];
                return $this;
            }
            return $this->{$name};
        }
    }

    public function offsetSet($offset, $value)
    {
       return $this->__set($offset, $value);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }
}