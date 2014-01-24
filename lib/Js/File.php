<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js;

class File extends \Js\File\Path
{
	public static function createTemp()
	{
		$path = str_replace(DIRECTORY_SEPARATOR, '/', tempnam(sys_get_temp_dir(), 'temp_'));
		if (!$path) {
			throw new \Js\Compressor\Exception('Could not create tempoary file');
		}
		return new \Js\File($path);
	}
	
	public function open($mode = 'r', $class = 'Js\File\Data')
	{
		return new $class($this->toString(), $mode);
	}

	public function move(\Js\Dir $dir, $name = null, $upload = false)
	{
		$path = $dir->toString() . '/' . (isset($name)
			? $name
			: $this->toString(\Js\Path::BASENAME));
		$upload
			? move_uploaded_file($this->toString(), $path)
			: rename($this->toString(), $path);
		return new \Js\File($path);
	}

	public function copy(\Js\Dir $dir, $name = null)
	{
		$path = $dir->toString() . '/' . (isset($name)
			? $name
			: $this->toString(\Js\Path::BASENAME));
		copy($this->toString(), $path);
		return new \Js\File($path);
	}

	public function rename($name)
	{
		$path = $this->toString(\Js\Path::DIRNAME) . '/' . $name;
		rename($this->toString(), $path);
		return new \Js\File($path);
	}

	public function delete()
	{
		unlink($this->toString());
		return $this;
	}

	public function chmod($mode)
	{
		chmod($this->toString(), $mode);
		return $this;
	}

	public function touch(\DateTime $modifyTime = null, \DateTime $accessTime = null)
	{
		touch($this->toString(), $modifyTime->format('U'), $accessTime->format('U'));
		return $this;
	}

	public function getSize()
	{
		return filesize($this->toString());
	}

	public function getHash($type)
	{
		return hash_file($type, $this->toString());
	}

	public function getDir()
	{
		return new \Js\Dir($this->toString(\Js\Path::DIRNAME));
	}

	public function getAccessTime()
	{
		return new \DateTime('@' . fileatime($this->toString()));
	}

	public function getChangeTime()
	{
		return new \DateTime('@' . filectime($this->toString()));
	}

	public function getModifyTime()
	{
		return new \DateTime('@' . filemtime($this->toString()));
	}
}