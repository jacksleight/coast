<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Sitemap;

use DateTime;
use Coast\Xml;
use Coast\Model;
use Coast\Validator;
use Coast\Transformer;

class Url extends Model
{
    const CHANGEFREQUENCY_ALWAYS  = 'always';
    const CHANGEFREQUENCY_HOURLY  = 'hourly';
    const CHANGEFREQUENCY_DAILY   = 'daily';
    const CHANGEFREQUENCY_WEEKLY  = 'weekly';
    const CHANGEFREQUENCY_MONTHLY = 'monthly';
    const CHANGEFREQUENCY_YEARLY  = 'yearly';
    const CHANGEFREQUENCY_NEVER   = 'never';

    protected $url;

    protected $modifyDate;

    protected $changeFrequency;

    protected $priority;

    protected static function _metadataStaticBuild()
    {
        return parent::_metadataStaticBuild()
            ->properties([
                'url' => [
                    'transformer' => (new Transformer())
                        ->break()
                        ->url(),
                    'validator' => (new Validator())
                        ->set()
                        ->break()
                        ->object('Coast\Url'),
                ],
                'modifyDate' => [
                    'transformer' => (new Transformer())
                        ->break()
                        ->dateTime(),
                    'validator' => (new Validator())
                        ->break()
                        ->object('DateTime'),
                ],
                'changeFrequency' => [
                    'validator' => (new Validator())
                        ->break()
                        ->in([
                            self::CHANGEFREQUENCY_ALWAYS,
                            self::CHANGEFREQUENCY_HOURLY,
                            self::CHANGEFREQUENCY_DAILY,
                            self::CHANGEFREQUENCY_WEEKLY,
                            self::CHANGEFREQUENCY_MONTHLY,
                            self::CHANGEFREQUENCY_YEARLY,
                            self::CHANGEFREQUENCY_NEVER,
                        ]),
                ],
                'priority' => [
                    'validator' => (new Validator())
                        ->break()
                        ->decimal()
                        ->range(0, 1),
                ],
            ]);
    }

    public function toXml()
    {
        $xml = new Xml('<?xml version="1.0" encoding="UTF-8"?><url/>');
        
        $xml->addChild('loc', $this->url->toString());
        if (isset($this->modifyDate)) {
            $xml->addChild('lastmod', $this->modifyDate->format(DateTime::W3C));
        }
        if (isset($this->changeFrequency)) {
            $xml->addChild('changefreq', $this->changeFrequency);
        }
        if (isset($this->priority)) {
            $xml->addChild('priority', $this->priority);
        }

        return $xml;
    }
}