<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Xml;

class Sitemap extends Xml
{
    const CHANGEFREQ_ALWAYS  = 'always';
    const CHANGEFREQ_HOURLY  = 'hourly';
    const CHANGEFREQ_DAILY   = 'daily';
    const CHANGEFREQ_WEEKLY  = 'weekly';
    const CHANGEFREQ_MONTHLY = 'monthly';
    const CHANGEFREQ_YEARLY  = 'yearly';
    const CHANGEFREQ_NEVER   = 'never';

    public function __construct()
    {
        parent::__construct('urlset');
        $this->_root->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }

    public function add(\Coast\Url $loc, \DateTime $lastmod = null, $changefreq = null, $priority = null)
    {
        $url = $this->_root->addChild('url');
        $loc = $url->addChild('loc', (string) $loc);
        if (isset($lastmod)) {
            $url->addChild('lastmod', $lastmod->format(\DateTime::W3C));
        }
        if (isset($changefreq)) {
            $url->addChild('changefreq', $changefreq);
        }
        if (isset($priority)) {
            $url->addChild('priority', number_format($priority, 1, '.', null));
        }
    }
}