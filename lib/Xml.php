<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use SimpleXMLElement;

class Xml extends SimpleXMLElement
{
    public function appendChild($child)
    {
        $node = dom_import_simplexml($this);
        $node->appendChild($node->ownerDocument->importNode(dom_import_simplexml($child), true));
        return $this;
    }

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

    public function __toString()
    {
        return $this->toString();
    }

    public function writeFile(\Coast\File $file)
    {
        return $this->asXML((string) $file);
    }
}