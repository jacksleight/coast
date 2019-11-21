<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Closure;

trait DateTimeTrait
{
    protected static $_jsonSerializer = false;

    protected $_mode = 'datetime';

    public static function jsonSerializer(Closure $value = null)
    {
        if (func_num_args() > 0) {
            self::$_jsonSerializer = $value;
        }
        return self::$_jsonSerializer;
    }

    public function mode($value = null)
    {
        if (func_num_args() > 0) {
            $this->_mode = $value;
            return $this;
        }
        return $this->_mode;
    }

    public function jsonSerialize()
    {
        if (isset(self::$_jsonSerializer)) {
            $func = self::$_jsonSerializer;
            return $func($this);
        }
        return parent::jsonSerialize();
    }
}

if (class_exists('Carbon\Carbon')) {
    class DateTime extends \Carbon\Carbon
    {
        const MODE_DATE       = 'date';
        const MODE_TIME       = 'time';
        const MODE_DATETIME   = 'datetime';

        const FORMAT_DATE     = 'Y-m-d';
        const FORMAT_TIME     = 'H:i:s';
        const FORMAT_DATETIME = 'Y-m-d H:i:s';

        use DateTimeTrait;
    }
} else {
    class DateTime extends \DateTime
    {
        const MODE_DATE       = 'date';
        const MODE_TIME       = 'time';
        const MODE_DATETIME   = 'datetime';

        const FORMAT_DATE     = 'Y-m-d';
        const FORMAT_TIME     = 'H:i:s';
        const FORMAT_DATETIME = 'Y-m-d H:i:s';

        use DateTimeTrait;        
    }
}