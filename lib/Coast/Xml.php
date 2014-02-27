<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

abstract class Xml
{
    protected $_xml;

    public function string($value = null)
    {
        if (isset($value)) {
            $this->_xml = new \SimpleXMLElement($value);
            return $this;
        }
        return $this->_xml->asXML();
    }

    public function readFile(\Coast\File $file)
    {
        $this->_xml = new \SimpleXMLElement($file->name(), 0, true);
        return $this;
    }

    public function writeFile(\Coast\File $file = null)
    {
        return $this->_xml->asXML($file->name());
    }

    public function __toString()
    {
        return $this->string();
    }
}