<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use ArrayAccess;
use Iterator;
use Countable;

class Collection implements ArrayAccess, Iterator, Countable
{
    protected $_array = [];

    protected $_position = 0;

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
        $this->_position = 0;
    }

    public function current() 
    {
        return $this->__get($this->_position);
    }

    public function key() 
    {
        return $this->_position;
    }

    public function next() 
    {
        ++$this->_position;
    }

    public function valid() 
    {
        return $this->__isset($this->_position);
    }   

    public function count() 
    { 
        return count($this->_array); 
    } 
}