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

class Content extends Model
{
    protected $_type;

    protected $format = 'html';

    protected $content;

    protected static function _metadataStaticBuild()
    {
        return parent::_metadataStaticBuild()
            ->properties([
                'format' => [
                    'type' => 'string',
                ],
                'content' => [
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
            'summary' => 'summary',
            'body'    => 'content',
        ];
        $xml = new Xml('<?xml version="1.0" encoding="UTF-8"?><' . $roots[$this->_type] . '/>');
        
        $xml->addCData($this->content)->addAttribute('type', $this->format);
        
        return $xml;
    }
}