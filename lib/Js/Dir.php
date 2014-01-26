<?php
/* 
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js;

class Dir extends \Js\File\Path implements \IteratorAggregate
{
	public function __construct($path, $mode = null)
	{
		parent::__construct($path);
		if (isset($mode)) {
			$this->make($mode);
		}
	}

	public function getIterator($recursive = false, $mode = \RecursiveIteratorIterator::LEAVES_ONLY, $flags = 0)
	{
		return new \Js\Dir\Iterator($this->toString(), $recursive, $mode, $flags);
	}

	public function make($mode = null)
	{
		$stack = explode("/", $this->toString());
		$parts = array();
		while (count($stack) > 0) {
			array_push($parts, array_shift($stack));
			$create = implode("/", $parts);
			if (strlen($create) == 0) {
				continue;
			}
			if (!is_dir($create)) {
				if (mkdir($create) && isset($mode)) {
					chmod($create, $mode);
				}
			}
		}
		return $this;
	}

	public function delete($recursive = false)
	{
		if ($recursive) {
			foreach ($this->getIterator(true, \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
				$path->delete();
			}
		}
		rmdir($this->toString());
		return $this;
	}

	public function chmod($mode, $recursive = false)
	{
		if ($recursive) {
			foreach ($this->getIterator(true, \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
				$path->chmod($mode);
			}
		}
		chmod($this->toString(), $mode);
		return $this;
	}

	public function getSize($recursive = false)
	{
		$size = 0;
		foreach ($this->getIterator($recursive) as $path) {
			if (!$path->isFile()) {
				continue;
			}
			$size += $path->getSize();
		}
		return $size;
	}

	public function hasFile($path)
	{
		return is_file("{$this->toString()}/{$path}");
	}

	public function getFile($path)
	{
		return new \Js\File("{$this->toString()}/{$path}");
	}

	public function hasDir($path)
	{
		return is_dir("{$this->toString()}/{$path}");
	}

	public function getDir($path, $mode = null)
	{
		return new \Js\Dir("{$this->toString()}/{$path}", $mode);
	}
}