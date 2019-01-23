<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use ArrayAccess;
use SeekableIterator;
use Countable;
use Serializable;
use JsonSerializable;

class Collection implements ArrayAccess, SeekableIterator, Countable, Serializable, JsonSerializable
{
    protected $_array = [];

    public function __construct(array $array = [])
    {
        $this->_array = $array;
    }

    public function toArray()
    {
        return $this->_array;
    }

    public function fromArray(array $array)
    {
        $this->_array = $array;
        return $this;
    }

    public function __set($key, $value)
    {
        if (isset($key)) {
            $this->_array[$key] = $value;
        } else {
            $this->_array[] = $value;
        }
    }

    public function __get($key)
    {
        return $this->_array[$key];
    }

    public function __isset($key)
    {
        return isset($this->_array[$key]);
    }

    public function __unset($key)
    {
        unset($this->_array[$key]);
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

    public function rewind() 
    {
        reset($this->_array);
    }

    public function current() 
    {
        return current($this->_array);
    }

    public function key() 
    {
        return key($this->_array);
    }

    public function next() 
    {
        next($this->_array);
    }

    public function valid() 
    {
        return key($this->_array) !== null;
    } 

    public function seek($position) 
    {
        do {
            if ($position === key($this->_array)) {
                return true;
            }
        } while (next($this->_array));
        return false;
    }

    public function count() 
    { 
        return count($this->_array); 
    }

    public function serialize()
    {
        return serialize($this->_array);
    }

    public function unserialize($array)
    {
        $this->_array = unserialize($array);
    }

    public function jsonSerialize()
    {
        return $this->_array;
    }
}