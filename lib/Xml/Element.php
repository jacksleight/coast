<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Xml;

use SimpleXMLElement;

class Element extends SimpleXMLElement
{
    public function addCData($value)
    {
        $node = dom_import_simplexml($this);
        $node->appendChild($node->ownerDocument->createCDATASection($value));
        return $this;
    }

	public function toArray()
	{
		$xml = simplexml_load_string($this->asXML(), 'SimpleXMLElement', LIBXML_NOCDATA);
		return json_decode(json_encode((array) $xml), true);
	}

    public function toString()
    {
        return $this->asXML();
    }

    public function writeFile(\Coast\File $file)
    {
        return $this->asXML((string) $file);
    }

    public function __toString()
    {
        return $this->toString();
    }
}