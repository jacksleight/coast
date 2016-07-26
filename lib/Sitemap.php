<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Xml;
use Coast\Model;
use Coast\Collection;

class Sitemap extends Model
{
    protected $urls;

    protected static function _metadataStaticBuild()
    {
        return parent::_metadataStaticBuild()
            ->properties([
                'urls' => [
                    'type'       => Model::TYPE_MANY,
                    'isCreate'   => true,
                    'isTraverse' => true,
                    'className'  => 'Coast\Sitemap\Url',
                ],
            ]);
    }

    public function __construct()
    {
        $this->urls = new Collection();
    }

    public function toXml()
    {
        $xml = new Xml('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($this->urls as $url) {
            $xml->appendChild($url->toXml());
        }
        return $xml;
    }
}