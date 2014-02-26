<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Sitemap extends \Coast\Xml\Wrapper
{
    const CHANGES_ALWAYS  = 'always';
    const CHANGES_HOURLY  = 'hourly';
    const CHANGES_DAILY   = 'daily';
    const CHANGES_WEEKLY  = 'weekly';
    const CHANGES_MONTHLY = 'monthly';
    const CHANGES_YEARLY  = 'yearly';
    const CHANGES_NEVER   = 'never';

    public function __construct()
    {
        parent::__construct('urlset');
        $this->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }

    public function add(\Coast\Url $loc, \DateTime $modified = null, $changes = null, $priority = null)
    {
        if (is_array($changes)) {
            list($since, $count) = $changes;
            if ($count == 0) {
                $changes = self::CHANGES_NEVER;
            } else {
                $ratio = (((new \DateTime())->getTimestamp() - $since->getTimestamp()) / 3600) / $count;
                $intervals = [
                    self::CHANGES_YEARLY  => 8760,
                    self::CHANGES_MONTHLY => 730,
                    self::CHANGES_WEEKLY  => 168,
                    self::CHANGES_DAILY   => 24,
                    self::CHANGES_HOURLY  => 1,
                    self::CHANGES_ALWAYS  => 0,
                ];
                foreach ($intervals as $changes => $interval) {
                    if ($ratio >= $interval) {
                        break;
                    }
                }
            }
        }
        $url = $this->addChild('url');
        $loc = $url->addChild('loc', $loc->name());
        if (isset($modified)) {
            $url->addChild('lastmod', $modified->format(\DateTime::W3C));
        }
        if (isset($changes)) {
            $url->addChild('changefreq', $changes);
        }
        if (isset($priority)) {
            $url->addChild('priority', number_format($priority, 1, '.', null));
        }
    }
}