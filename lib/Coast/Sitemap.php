<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Sitemap extends \Coast\Dom\Document
{
	const CHANGES_ALWAYS	= 'always';
	const CHANGES_HOURLY	= 'hourly';
	const CHANGES_DAILY		= 'daily';
	const CHANGES_WEEKLY	= 'weekly';
	const CHANGES_MONTHLY	= 'monthly';
	const CHANGES_YEARLY	= 'yearly';
	const CHANGES_NEVER		= 'never';

	protected $_root;

	public function __construct()
	{
		parent::__construct('1.0', 'UTF-8');
		$this->formatOutput = false;

		$this->_root = $this->createElement('urlset', [
			'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
		]);
		$this->appendChild($this->_root);
	}

	public function add($location, \DateTime $modified = null, $changes = null, $priority = null)
	{
		if (is_array($changes)) {
			list($since, $count) = $changes;
			if ($count == 0) {
				$changes = self::CHANGES_NEVER;
			} else {
				$ratio = ((\Coast\DateTime::now()->format('U') - $since->format('U')) / 3600) / $count;
				$intervals = [
					self::CHANGES_YEARLY	=> 8760,
					self::CHANGES_MONTHLY	=> 730,
					self::CHANGES_WEEKLY	=> 168,
					self::CHANGES_DAILY		=> 24,
					self::CHANGES_HOURLY	=> 1,
					self::CHANGES_ALWAYS	=> 0,
				];
				foreach ($intervals as $changes => $interval) {
					if ($ratio >= $interval) {
						break;
					}
				}
			}
		}
		$url = $this->createElement('url', [
			$this->createElement('loc', $location),
		]);
		if (isset($modified)) {
			$url->appendChild($this->createElement('lastmod', $modified->format(\DateTime::W3C)));
		}
		if (isset($changes)) {
			$url->appendChild($this->createElement('changefreq', $changes));
		}
		if (isset($priority)) {
			$url->appendChild($this->createElement('priority', number_format($priority, 1, '.', null)));
		}
		$this->_root->appendChild($url);
	}
}