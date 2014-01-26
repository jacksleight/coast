<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js\File\Data;

class Log extends \Js\File\Data
{
	public function __construct($name, $mode = 'a+')
	{
		parent::__construct($name, $mode);
	}

	public function add($value)
	{
		$date = new \DateTime();
		$this->put('[' . $date->format('d-M-Y H:i:s') . '] ' . $value);
		return $this;
	}
}