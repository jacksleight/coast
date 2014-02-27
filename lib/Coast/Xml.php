<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

abstract class Xml
{
    protected $_xml;

    public function string()
    {
        return $this->_xml->asXML();
    }

    public function file(\Coast\File $file)
    {
        return $this->_xml->asXML($file->name());
    }

    public function __toString()
    {
        return $this->string();
    }
}