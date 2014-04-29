<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

trait Options
{
    protected $_opts = [];

    public function opt($name, $value = null)
    {
        if (isset($value)) {
            $this->_opts[$name] = $value;
            return $this;
        }
        return isset($this->_opts[$name])
            ? $this->_optInit($name, $this->_opts[$name])
            : null;
    }

    public function opts(array $opts = null)
    {
        if (isset($opts)) {
            foreach ($opts as $name => $value) {
                $this->_opts[$name] = $value;
            }
            return $this;
        }
        $opts = [];
        foreach ($this->_opts as $name => $value) {
            $opts[$name] = $this->_optInit($name, $value);
        }
        return $this->_opts;
    }

    protected function _optInit($name, $value)
    {
        return $value;
    }
}