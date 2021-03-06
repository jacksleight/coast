<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\View;

class Content
{
    protected $_params = [];

    protected $_blocks = [];

    public function __construct(array $blocks = array())
    {
        $this->_blocks = $blocks;
    }

    public function param($name, $value = null)
    {
        if (func_num_args() > 1) {
            $this->_params[$name] = $value;
            return $this;
        }
        return isset($this->_params[$name])
            ? $this->_params[$name]
            : null;
    }

    public function params(array $querys = null)
    {
        if (func_num_args() > 0) {
            foreach ($querys as $name => $value) {
                $this->param($name, $value);
            }
            return $this;
        }
        return $this->_params;
    }

    public function block($name, $value = null)
    {
        if (func_num_args() > 1) {
            if (isset($name)) {
                $this->_blocks[$name] = $value;
            } else {
                $this->_blocks[] = $value;
            }
            return $this;
        }
        return isset($this->_blocks[$name])
            ? $this->_blocks[$name]
            : null;
    }

    public function blocks(array $blocks = null)
    {
        if (func_num_args() > 0) {
            foreach ($blocks as $name => $value) {
                $this->block($name, $value);
            }
            return $this;
        }
        return $this->_blocks;
    }

    public function next()
    {
        $temp = array_combine($keys = array_keys($this->_blocks), $keys);
        $temp[] = null;
        end($temp);
        return key($temp);
    }

    public function __set($name, $value)
    {
        $this->block($name, $value);
    }

    public function __get($name)
    {
        return $this->block($name);
    }

    public function __isset($name)
    {
        return $this->block($name) !== null;
    }

    public function __unset($name)
    {
        $this->block($name, null);
    }

    public function toString()
    {
        return implode($this->_blocks);
    }

    public function __toString()
    {
        return $this->toString();
    }
}