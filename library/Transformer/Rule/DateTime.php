<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast\Transformer\Rule;

class DateTime extends Rule
{
    protected $_format;

    protected $_timezone;

    public function __construct($format = null, $timezone = null)
    {
        $this->format($format);
        $this->timezone($timezone);
    }

    public function format($format = null)
    {
        if (func_num_args() > 0) {
            $this->_format = $format;
            return $this;
        }
        return $this->_format;
    }

    public function timezone($timezone = null)
    {
        if (func_num_args() > 0) {
            $this->_timezone = $timezone;
            return $this;
        }
        return $this->_timezone;
    }

    protected function _transform($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }
        $defaultTimezone = new \DateTimezone(date_default_timezone_get());
        $timezone = isset($this->_timezone)
            ? new \DateTimezone($this->_timezone)
            : $defaultTimezone;
        if (isset($this->_format)) {
            $date = \DateTime::createFromFormat($this->_format, $value, $timezone);
            if ($date === false) {
                return $value;
            }
        } else {
            try {
                $date = new \DateTime($value, $timezone);
            } catch (\Exception $e) {
                return $value;
            }
        }
        $date->setTimezone($defaultTimezone);
        return $date;
    }
}