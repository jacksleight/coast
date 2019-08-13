<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Transformer\Rule;

use Coast\Transformer\Rule;

class DateTimeRange extends Rule
{
    protected $_dateTimeTransformer;

    public function __construct($format = null, $timezone = null)
    {
        $this->_dateTimeTransformer = new Rule\DateTime($format, $timezone);
    }

    public function format($format = null)
    {
        return $this->_dateTimeTransformer->format($format);
    }

    public function timezone($timezone = null)
    {
        return $this->_dateTimeTransformer->format($timezone);
    }

    protected function _transform($value)
    {
        if (is_string($value)) {
            $value = explode('/', $value, 2) + [null, null];
        }
        if (!$value[0] instanceof \DateTime) {
            $value[0] = strlen($value[0])
                ? $this->_dateTimeTransformer->transform($value[0])
                : new \DateTime('1000-01-01 00:00:00');
            if ($this->_dateTimeTransformer->format() === \Coast\DATE) {
                $value[0]->setTime(0, 0, 0);
            }
        }
        if (!$value[1] instanceof \DateTime) {
            $value[1] = strlen($value[1])
                ? $this->_dateTimeTransformer->transform($value[1])
                : new \DateTime('9999-12-31 23:59:59');
            if ($this->_dateTimeTransformer->format() === \Coast\DATE) {
                $value[1]->setTime(23, 59, 59);
            }
        }
        return $value;
    }
}