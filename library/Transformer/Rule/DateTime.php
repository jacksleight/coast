<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast\DateTime as CoastDateTime;
use Coast\Transformer\Rule;

class DateTime extends Rule
{
    protected $_mode;

    protected $_format;

    protected $_timezone;

    public function __construct($mode = CoastDateTime::MODE_DATETIME, $format = null, $timezone = null)
    {
        $this->mode($mode);
        $this->format($format);
        $this->timezone($timezone);
    }

    public function mode($mode = null)
    {
        if (func_num_args() > 0) {
            $this->_mode = $mode;
            return $this;
        }
        return $this->_mode;
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
        $defaultTimezone = new \DateTimezone(date_default_timezone_get());
        $timezone = isset($this->_timezone)
            ? new \DateTimezone($this->_timezone)
            : $defaultTimezone;
        if (is_scalar($value)) {
            if (isset($this->_format)) {
                $date = CoastDateTime::createFromFormat($this->_format, $value, $timezone);
                $date->mode($this->_mode);
                if ($date === false) {
                    return $value;
                }
            } else {
                try {
                    $date = new CoastDateTime($value, $timezone);
                    $date->mode($this->_mode);
                } catch (\Exception $e) {
                    return $value;
                }
            }
            $date->setTimezone($defaultTimezone);
        } else if (is_array($value)) {
            $timezone = isset($value['timezone'])
                ? new \DateTimezone($value['timezone'])
                : $timezone;
            try {
                $date = new CoastDateTime($value['date'], $timezone);
                $date->mode($this->_mode);
            } catch (\Exception $e) {
                return $value;
            }
            $date->setTimezone($defaultTimezone);
        } else {
            return $value;
        }
        return $date;
    }
}