<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js\File;

abstract class Path extends \Js\Path
{
	public function exists()
	{
		return file_exists($this->toString());
	}

	public function isFile()
	{
		return is_file($this->toString());
	}

	public function isDir()
	{
		return is_dir($this->toString());
	}

	public function isReadable()
	{
		return is_readable($this->toString());
	}

	public function isWritable()
	{
		return is_writable($this->toString());
	}

	public function getPermissions()
	{
		return substr(sprintf('%o', fileperms($this->toString())), -4);
	}
	
	abstract public function delete();
	abstract public function chmod($mode);
}