<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Regex extends Rule
{
	protected $_regex;
	protected $_name;

	public function __construct($regex, $name = null)
	{
		$this->_regex = $regex;
		$this->_name  = $name;
	}

	public function name()
	{
		return isset($this->_name)
			? $this->_name
			: parent::name();
	}

	protected function _validate($value)
	{
		if (!preg_match($this->_regex, $value)) {
			$this->error();
		}
	}
}
