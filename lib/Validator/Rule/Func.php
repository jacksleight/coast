<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Func extends Rule
{
	protected $_func;
	protected $_name;

	public function __construct(callable $func, $name = null)
	{
		$this->_func = $func;
		$this->_name = $name;
	}

	public function name()
	{
		return isset($this->_name)
			? $this->_name
			: parent::name();
	}

	protected function _validate($value)
	{
		if (!call_user_func($this->_func, $value)) {
			$this->error();
		}
	}
}
