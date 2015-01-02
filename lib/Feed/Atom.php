<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Feed;

class Atom extends \Coast\Xml
{
    public function __construct($title, \Coast\Url $link, $author, \DateTime $updated)
    {
        $this->_xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><feed/>');
        $this->_xml->addAttribute('xmlns', 'http://www.w3.org/2005/Atom');

        $this->_xml->addChild('title', $title);
        $this->_xml->addChild('id', (string) $link);
        $this->_xml->addChild('link')->addAttribute('href', $link);
        $this->_xml->addChild('author')->addChild('name', $author);
        $this->_xml->addChild('updated', $updated->format(\DateTime::W3C));
    }

    public function add($title, \Coast\Url $link, \DateTime $updated, $summary = null)
    {
        $entry = $this->_xml->addChild('entry');
        $entry->addChild('id', (string) $link);
        $entry->addChild('title', $title);
        $entry->addChild('link')->addAttribute('href', $link);
        $entry->addChild('updated', $updated->format(\DateTime::W3C));
        if (isset($summary)) {
            $entry->addChild('summary', $summary);
        }
    }
}