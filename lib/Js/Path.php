<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js;

class Path
{
	const ALL		= 0;
	const DIRNAME	= PATHINFO_DIRNAME;
	const BASENAME	= PATHINFO_BASENAME;
	const EXTENSION	= PATHINFO_EXTENSION;
	const FILENAME	= PATHINFO_FILENAME;

	protected $_name;

	public function __construct($name)
	{
		$name = str_replace('\\', '/', $name);
		$name = preg_replace('/\/+/', '/', $name);
		$name = $name != '/'
			? rtrim($name, '/')
			: $name;
		$this->_name = $name;
	}

	public function toString($part = null)
	{
		return isset($part)
			? ($part == self::ALL ? pathinfo($this->_name) : pathinfo($this->_name, $part))
			: $this->_name;
	}

	public function __toString()
	{
		return $this->toString();
	}

	public function isWithin(\Js\Path $target)
	{
		$path = $this->toString();
		$parts = \explode(PATH_SEPARATOR, $target->toString());	
		foreach ($parts as $part) {
			if (\preg_match('/^' . \preg_quote($part, '/') . '/', $path)) {
				return true;
			}
		}
		return false;
	}

	public function fromRelative(\Js\Path $target)
	{
		if (!$this->isAbsolute() || !$target->isRelative()) {
			throw new \Exception("Source path '" . $this->toString() . "' is not absolute or target path '" . $target->toString() . "' is not relative");
		}

		$source	= explode('/', $this->toString());
		$target	= explode('/', $target->toString());
		
		$name = $source;
		array_pop($name);
		foreach ($target as $part) {
			if ($part == '..') {
				array_pop($name);
			} elseif ($part != '.' && $part != '') {
				$name[] = $part;
			}
		}
		$name = implode('/', $name);

		$class = get_class($this);
		return new $class($name);
	}

	public function toRelative(\Js\Path $target)
	{
		if (!$this->isAbsolute() || !$target->isAbsolute()) {
			throw new \Exception("Source path '" . $this->toString() . "' is not absolute or target path '" . $target->toString() . "' is not absolute");
		}
		
		$source	= explode('/', $this->toString());
		$target	= explode('/', $target->toString());

		$name = $target;
		foreach ($source as $i => $part) {
		    if ($part == $target[$i]) {
				array_shift($name);
			} else {
				$name = array_pad($name, (count($name) + (count($source) - $i) - 1) * -1, '..');
				break;
			}
		}
		$name = implode('/', $name);

		$class = get_class($this);
		return new $class($name);
	}

	public function isAbsolute()
	{
		return substr($this->toString(), 0, 1) == '/';
	}

	public function isRelative()
	{
		return !$this->isAbsolute();
	}
}