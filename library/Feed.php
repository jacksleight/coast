<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Xml;
use Coast\Model;
use Coast\Collection;

class Feed extends Model
{
    protected $id;

    protected $url;

    protected $urlFeed;

    protected $title;

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
                'url' => [
                    'type' => 'url',
                ],
                'urlFeed' => [
                    'type' => 'url',
                ],
                'title' => [
                    'type' => 'string',
                ],
                'updateDate' => [
                    'type' => 'datetime',
                ],
                'author' => [
                    'type'            => Model::TYPE_ONE,
                    'className'       => 'Coast\Feed\Person',
                    'classArgs'       => ['author'],
                    'traverse'        => [Model::TRAVERSE_SET, Model::TRAVERSE_GET, Model::TRAVERSE_CREATE],
                ],
                'items' => [
                    'type'            => Model::TYPE_MANY,
                    'className'       => 'Coast\Feed\Item',
                    'classArgs'       => ['author'],
                    'traverse'        => [Model::TRAVERSE_SET, Model::TRAVERSE_GET, Model::TRAVERSE_CREATE],
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
        $xml->addChild('link')->addAttribute('href', $this->url->toString());
        $urlFeed = $xml->addChild('link');
        $urlFeed->addAttribute('rel', 'self');
        $urlFeed->addAttribute('href', $this->urlFeed->toString());
        $xml->addChild('title')->addCData($this->title);
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