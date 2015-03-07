<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Xml
{
    protected $_root;

    public function __construct($root, $version = '1.0', $encoding = 'UTF-8')
    {
        $this->_root = new Xml\Element('<?xml version="' . $version . '" encoding="' . $encoding . '"?><' . $root . '/>');
    }

    public function __call($name, $args)
    {
        return call_user_func_array([$this->_root, $name], $args);
    }

    public function __toString()
    {
        return $this->_root->__toString();
    }
}