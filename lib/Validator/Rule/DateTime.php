<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class DateTime extends Rule
{
	protected $_format;

	public function __construct($format)
	{
		$this->format($format);
	}

    public function format($format = null)
    {
        if (func_num_args() > 0) {
            $this->_format = $format;
            return $this;
        }
        return $this->_format;
    }

	protected function _validate($value)
	{
		if ($value instanceof \DateTime) {
			return;
		}
		$date	= \DateTime::createFromFormat($this->_format, $value);
		$errors	= \DateTime::getLastErrors();         
    	if ($errors['warning_count'] || $errors['error_count']) {
    		$this->error();
    	}
	}
}