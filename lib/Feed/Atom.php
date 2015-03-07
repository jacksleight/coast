<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Feed;

use Coast\Xml;

class Atom extends Xml
{
    public function __construct($title, \Coast\Url $link, $author, \DateTime $updated)
    {
        parent::__construct('feed');
        $this->_root->addAttribute('xmlns', 'http://www.w3.org/2005/Atom');

        $this->_root->addChild('title', $title);
        $this->_root->addChild('id', (string) $link);
        $this->_root->addChild('link')->addAttribute('href', $link);
        $this->_root->addChild('author')->addChild('name', $author);
        $this->_root->addChild('updated', $updated->format(\DateTime::W3C));
    }

    public function add($title, \Coast\Url $link, \DateTime $updated, $summary = null, $content = null)
    {
        $entry = $this->_root->addChild('entry');
        $entry->addChild('id', (string) $link)->addAttribute('test', '123');
        $entry->addChild('title')->addCData($title);
        $entry->addChild('link')->addAttribute('href', $link);
        $entry->addChild('updated', $updated->format(\DateTime::W3C));
        if (isset($summary)) {
            $entry->addChild('summary')->addCData($summary);
        }
        if (isset($content)) {
            $entry->addChild('content')->addCData($content);
        }
    }
}