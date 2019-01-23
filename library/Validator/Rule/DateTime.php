<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class DateTime extends Rule
{
	protected $_format;

	public function __construct($format = null)
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
        if (!is_scalar($value)) {
            $this->error();
            return;
        }
        if (isset($this->_format)) {
            $date = \DateTime::createFromFormat($this->_format, $value);
    		if ($date === false) {
        		$this->error();
        	}
        } else {
            try {
                $date = new \DateTime($value);
            } catch (\Exception $e) {
                $this->error();
            }
        }
	}
}