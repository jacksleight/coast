<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Feed;

class Atom extends \Coast\Xml\Wrapper
{
    protected $_root;

    public function __construct($title, \Coast\Url $link, $author, \DateTime $updated)
    {
        parent::__construct('feed');
        $this->addAttribute('xmlns', 'http://www.w3.org/2005/Atom');

        $this->addChild('title', $title);
        $this->addChild('id', $link->name());
        $this->addChild('link')->addAttribute('href', $link);
        $this->addChild('author')->addChild('name', $author);
        $this->addChild('updated', $updated->format(\DateTime::W3C));
    }

    public function add($title, \Coast\Url $link, \DateTime $updated, $summary = null)
    {
        $entry = $this->addChild('entry');
        $entry->addChild('id', $link->name());
        $entry->addChild('title', $title);
        $entry->addChild('link')->addAttribute('href', $link);
        $entry->addChild('updated', $updated->format(\DateTime::W3C));
        if (isset($summary)) {
            $entry->addChild('summary', $summary);
        }
    }
}