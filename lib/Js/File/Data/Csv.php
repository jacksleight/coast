<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\File\Data;

class Csv extends \Js\File\Data
{
	protected $_delimiter = ',';
	protected $_enclosure = '"';
	protected $_escape = '\\';

	public function setCharacters($delimiter = ',', $enclosure = '"', $escape = '\\')
	{
		$this->_delimiter = $delimiter;
		$this->_enclosure = $enclosure;
		$this->_escape = $escape;
		return $this;
	}

	public function getArray($length = 0)
	{
		return fgetcsv($this->_handle, $length, $this->_delimiter, $this->_enclosure, $this->_escape);
	}

	public function putArray($array)
	{
		fputcsv($this->_handle, $array, $this->_delimiter, $this->_enclosure);
		return $this;
	}
}