<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Feed extends \Coast\Dom\Document
{
    protected $_root;

    public function __construct($title, $link, $author, \DateTime $updated)
    {
        parent::__construct('1.0', 'UTF-8');
        $this->formatOutput = false;

        $this->_root = $this->createElement('feed', [
            'xmlns' => 'http://www.w3.org/2005/Atom',
        ], [
            $this->createElement('id', $link),
            $this->createElement('title', $title),
            $this->createElement('link', [
                'href' => $link,
            ]),
            $this->createElement('author', [
                $this->createElement('name', $author),
            ]),
            $this->createElement('updated', $updated->format(\DateTime::W3C)),
        ]);
        $this->appendChild($this->_root);
    }

    public function add($title, $link, \DateTime $updated, $summary = null)
    {
        $entry = $this->createElement('entry', [
            $this->createElement('id', $link),
            $this->createElement('title', $title),
            $this->createElement('link', [
                'href' => $link,
            ]),
            $this->createElement('updated', $updated->format(\DateTime::W3C)),
        ]);
        if (isset($summary)) {
            $entry->appendChild($this->createElement('summary', $summary));
        }
        $this->_root->appendChild($entry);
    }
}