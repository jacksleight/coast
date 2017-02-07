<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Feed;

use DateTime;
use Coast\Xml;
use Coast\Model;
use Coast\Validator;
use Coast\Transformer;

class Item extends Model
{
    protected $id;

    protected $url;

    protected $title;

    protected $updateDate;

    protected $author;

    protected $summary;

    protected $body;

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
                'title' => [
                    'type' => 'string',
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
                'summary' => [
                    'type'        => Model::TYPE_ONE,
                    'isConstruct' => true,
                    'isTraverse'  => true,
                    'construct'   => ['Coast\Feed\Content', 'summary'],
                ],
                'body' => [
                    'type'        => Model::TYPE_ONE,
                    'isConstruct' => true,
                    'isTraverse'  => true,
                    'construct'   => ['Coast\Feed\Content', 'body'],
                ],
            ]);
    }

    public function toXml()
    {
        $xml = new Xml('<?xml version="1.0" encoding="UTF-8"?><entry/>');
        
        $xml->addChild('id', $this->id);
        $xml->addChild('title')->addCData($this->title);
        $xml->addChild('link')->addAttribute('href', $this->url->toString());
        $xml->addChild('updated', $this->updateDate->format(\DateTime::W3C));
        if (isset($this->author)) {
            $xml->appendChild($this->author->toXml());
        }
        if (isset($this->summary)) {
            $xml->appendChild($this->summary->toXml());
        }
        if (isset($this->body)) {
            $xml->appendChild($this->body->toXml());
        }

        return $xml;
    }
}