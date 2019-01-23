<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Feed;

use DateTime;
use Coast\Xml;
use Coast\Model;
use Coast\Validator;
use Coast\Transformer;

class Person extends Model
{
    protected $_type;

    protected $name;

    protected static function _metadataStaticBuild()
    {
        return parent::_metadataStaticBuild()
            ->properties([
                'name' => [
                    'type' => 'string',
                ],
            ]);
    }

    public function __construct($type)
    {
        $this->_type = $type;
    }

    public function toXml()
    {
        $roots = [
            'author' => 'author',
        ];
        $xml = new Xml('<?xml version="1.0" encoding="UTF-8"?><' . $roots[$this->_type] . '/>');
        
        $xml->addChild('name')->addCData($this->name);
        
        return $xml;
    }
}