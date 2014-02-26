<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Xml;

abstract class Wrapper
{
	protected $_xml;

    public function __construct($root)
    {
        $this->_xml = new \Coast\Xml('<?xml version="1.0" encoding="UTF-8"?><' . $root . '/>');
    }

    public function xml()
    {
        return $this->_xml;
    }

    public function string()
    {
        return $this->asXML();
    }

    public function __toString()
    {
        return $this->string();
    }

    public function __call($name, $args = array())
    {
        return call_user_func_array([$this->_xml, $name], $args);
    }
}