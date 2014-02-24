<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App\View;

class Content
{
    protected $_data = [];

    public function __construct(array $data = array())
    {
        $this->_data = $data;
    }

    public function add($value, $name = null)
    {
        if (isset($name)) {
            $this->_data[$name] = $value;
        } else {
            $this->_data[] = $value;
        }
        return $this;
    }

    public function get($name)
    {
        return isset($this->_data[$name])
            ? $this->_data[$name]
            : null;
    }

    public function has($name)
    {
        return isset($this->_data[$name]);
    }

    public function string()
    {
        return implode($this->_data);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    public function __toString()
    {
        return $this->string();
    }
}