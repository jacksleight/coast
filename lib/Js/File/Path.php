<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
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