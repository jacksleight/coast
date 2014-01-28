<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE.  
 */

namespace Coast\Dom;

class Document extends \DOMDocument
{
	protected $_partial;
	protected $_xPath;

	public function __construct($version = null, $encoding = null)
	{
		parent::__construct($version, $encoding);
		$this->registerNodeClass('DOMDocument', 'Coast\Dom\Document');
		$this->registerNodeClass('DOMElement', 'Coast\Dom\Element');
	}

	public function loadHTML($source, $options = 0)
	{
		$source = str_replace("\r", '', $source);
		$this->_partial = strpos($source, '<!DOCTYPE html>') === false;
		if ($this->_partial) {
			$source = '
				<!DOCTYPE html>
				<html>
				<head>
					<meta charset="utf-8">
				</head>
				<body>' . $source . '</body>
				</html>';
		}		
		$source = preg_replace('/<meta charset="([^"]+)">/isu', '<meta http-equiv="Content-Type" content="text/html; charset=$1">', $source);
		libxml_use_internal_errors(true);
		if (version_compare(PHP_VERSION, '5.5', '>=')) {
			$result = parent::loadHTML($source, $options);
		} else {
			$result = parent::loadHTML($source);
		}
		libxml_use_internal_errors(false);
		return $result;
	}

	public function saveHTML()
	{
		$source = $this->saveXML($this, LIBXML_NOEMPTYTAG);
		if ($this->_partial) {
			preg_match('/<body>(.*)<\/body>/isu', $source, $match);
			$source = $match[1];
		}
		$source = preg_replace('/<!\[CDATA\[(.*?)\]\]>/isu', '$1', $source);
		$source = preg_replace('/<meta http-equiv="Content-Type" content="text\/html; charset=([^"]+)">/isu', '<meta charset="$1">', $source);
		$source = preg_replace('/<\?xml.*?\?>\n/iu', '', $source);
		$source = preg_replace('/<\/(area|base|basefont|br|col|frame|hr|img|input|isindex|link|meta|param|source)>/isu', '', $source);
		$source = preg_replace('/(<pre[^>]*>)\s*(<code[^>]*>)/isu', '$1$2', $source);
		$source = preg_replace('/(<\/code>)\s*(<\/pre>)/isu', '$1$2', $source);
		return $source;
	}

	public function createElement($name, $attributes = null, $children = null)
	{
		$element = parent::createElement($name);

		if (!is_array($attributes)) {
			$attributes = isset($attributes) ? [$attributes] : [];
		}
		if (!is_array($children)) {
			$children = isset($children) ? [$children] : [];
		}
		if (!\Coast\array_is_assoc($attributes)) {
			$children = $attributes;
			$attributes = [];
		}
		foreach ($attributes as $name => $value) {
			$element->setAttribute($name, $value);
		}
		foreach ($children as $child) {
			if (!$child instanceof \DOMElement) {
				$child = $this->createTextNode((string) $child);
			}
			$element->appendChild($child);
		}

		return $element;
	}

	public function getXPath()
	{
		if (!isset($this->_xPath)) {
			$this->_xPath = new \DOMXPath($this);
		}
		return $this->_xPath;
	}

	public function query($expression)
	{
		return $this->getXPath()->query($expression);
	}
}
