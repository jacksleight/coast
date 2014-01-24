<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
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