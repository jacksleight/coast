<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\File\Data;

class Csv extends \Coast\File\Data
{
	protected $_delimiter	= ',';
	protected $_enclosure	= '"';
	protected $_escape		= '\\';

	public function characters($delimiter = ',', $enclosure = '"', $escape = '\\')
	{
		$this->_delimiter	= $delimiter;
		$this->_enclosure	= $enclosure;
		$this->_escape		= $escape;
		return $this;
	}

	public function get($length = 0)
	{
		return fgetcsv($this->_handle, $length, $this->_delimiter, $this->_enclosure, $this->_escape);
	}

	public function put($array)
	{
		fputcsv($this->_handle, $array, $this->_delimiter, $this->_enclosure);
		return $this;
	}
}