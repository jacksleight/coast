<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Xml;
use Coast\Model;
use Coast\Collection;

class Feed extends Model
{
    protected $id;

    protected $title;

    protected $url;

    protected $updateDate;

    protected $author;

    protected $items;

    protected static function _metadataStaticBuild()
    {
        return parent::_metadataStaticBuild()
            ->properties([
                'id' => [
                    'type' => 'string',
                ],
                'title' => [
                    'type' => 'string',
                ],
                'url' => [
                    'type' => 'url',
                ],
                'updateDate' => [
                    'type' => 'datetime',
                ],
                'author' => [
                    'type'        => Model::TYPE_ONE,
                    'isConstruct' => true,
                    'isTraverse'  => true,
                    'construct'   => ['Coast\Feed\Person', 'author'],
                ],
                'items' => [
                    'type'        => Model::TYPE_MANY,
                    'isConstruct' => true,
                    'isTraverse'  => true,
                    'construct'   => 'Coast\Feed\Item',
                ],
            ]);
    }

    public function __construct()
    {
        $this->items = new Collection();
    }

    public function toXml()
    {
        $xml = new Xml('<?xml version="1.0" encoding="UTF-8"?><feed/>');
        $xml->addAttribute('xmlns', 'http://www.w3.org/2005/Atom');

        $xml->addChild('id', $this->id);
        $xml->addChild('title')->addCData($this->title);
        $xml->addChild('link')->addAttribute('href', $this->url->toString());
        $xml->addChild('updated', $this->updateDate->format(\DateTime::W3C));
        if (isset($this->author)) {
            $xml->appendChild($this->author->toXml());
        }
        
        foreach ($this->items as $item) {
            $xml->appendChild($item->toXml());
        }
        
        return $xml;
    }
}