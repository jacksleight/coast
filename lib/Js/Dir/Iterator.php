<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\Dir;

class Iterator implements \SeekableIterator
{
	protected $_spl;

	public function __construct($path, $recursive = false, $flags = null)
	{
		$this->_spl = !$recursive
			? new \FilesystemIterator($path, $flags)
			: new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), $flags);
	}

	public function __call($method, $args)
	{
		return call_user_func_array(array($this->_spl, $method), $args);
	}

	public function current()
	{
		$path = $this->_spl->current()->getPathname();
		return $this->_spl->isDir()
			? new \Js\Dir($path)
			: new \Js\File($path);
	}

	public function key()
	{
		return $this->_spl->key();
	}

	public function next()
	{
		return $this->_spl->next();
	}

	public function rewind()
	{
		return $this->_spl->rewind();
	}

	public function valid()
	{
		return $this->_spl->valid();
	}

	public function seek($position)
	{
		return $this->_spl->seek($position);
	}
}