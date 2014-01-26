<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js\File;

class Data extends \Js\File
{
	protected $_handle;

	public function __construct($name, $mode = 'r')
	{
		parent::__construct($name);
		$this->_handle = fopen($this->toString(), $mode);
	}

	public function getHandle()
	{
		return $this->_handle;
	}

	public function setWriteBuffer($buffer)
	{
		stream_set_write_buffer($this->_handle, $buffer);
		return $this;
	}

	public function close($class = 'Js\File')
	{
		fclose($this->_handle);
		return new $class($this->toString());
	}

	public function read($length = null)
	{
		$size = $this->getSize();
		return fread($this->_handle, isset($length) ? $length : ($size ? $size : 1));
	}

	public function get($length = null)
	{
		return isset($length)
			? fgets($this->_handle, $length)
			: fgets($this->_handle);
	}

	public function write($string, $length = null)
	{
		isset($length)
			? fwrite($this->_handle, $string, $length)
			: fwrite($this->_handle, $string);
		return $this;
	}

	public function put($string, $length = null)
	{
		$this->write($string, $length) . $this->write("\n");
		return $this;
	}

	public function seek($offset, $whence = SEEK_SET)
	{
		fseek($this->_handle, $offset, $whence);
		return $this;
	}

	public function truncate($length = 0)
	{
		ftruncate($this->_handle, $length);
		return $this;
	}
}