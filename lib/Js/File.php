<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js;

class File extends \Js\File\Path
{
	public static function tempoary()
	{
		$path = str_replace(DIRECTORY_SEPARATOR, '/', tempnam(sys_get_temp_dir(), 'temp_'));
		if (!$path) {
			throw new \Exception('Could not create tempoary file');
		}
		return new \Js\File($path);
	}
	
	public function open($mode = 'r', $class = 'Js\File\Data')
	{
		return new $class($this->string(), $mode);
	}

	public function move(\Js\Dir $dir, $name = null, $upload = false)
	{
		$path = $dir->string() . '/' . (isset($name)
			? $name
			: $this->string(\Js\Path::BASENAME));
		$upload
			? move_uploaded_file($this->string(), $path)
			: rename($this->string(), $path);
		return new \Js\File($path);
	}

	public function copy(\Js\Dir $dir, $name = null)
	{
		$path = $dir->string() . '/' . (isset($name)
			? $name
			: $this->string(\Js\Path::BASENAME));
		copy($this->string(), $path);
		return new \Js\File($path);
	}

	public function rename($name)
	{
		$path = $this->string(\Js\Path::DIRNAME) . '/' . $name;
		rename($this->string(), $path);
		return new \Js\File($path);
	}

	public function delete()
	{
		unlink($this->string());
		return $this;
	}

	public function permissions($mode = null)
	{
		if (isset($mode)) {
			chmod($this->string(), $mode);
			return $this;
		}
		return parent::permissions();
	}

	public function touch(\DateTime $modify = null, \DateTime $access = null)
	{
		touch($this->string(), $modify->format('U'), $access->format('U'));
		return $this;
	}

	public function size()
	{
		return filesize($this->string());
	}

	public function hash($type)
	{
		return hash_file($type, $this->string());
	}

	public function dir($mode = null)
	{
		return new \Js\Dir($this->string(\Js\Path::DIRNAME), $mode);
	}

	public function access()
	{
		return new \DateTime('@' . fileatime($this->string()));
	}

	public function change()
	{
		return new \DateTime('@' . filectime($this->string()));
	}

	public function modify()
	{
		return new \DateTime('@' . filemtime($this->string()));
	}
}