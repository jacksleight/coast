<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;
use Coast\File as CoastFile; 

class File extends Rule
{
    const VALID    = 'valid';
    const EXISTS   = 'exists';
    const SIZE     = 'size';
    const TYPE     = 'type';
    const READABLE = 'readable';
    const WRITABLE = 'writable';

    protected $_size     = null;
    protected $_types    = null;
    protected $_readable = null;
    protected $_writable = null;

    public function __construct($size = null, $types = null, $readable = true, $writable = false)
    {
        $this->size($size);
        $this->types($types);
        $this->readable($readable);
        $this->writable($writable);
    }

    public function size($size = null)
    {
        if (func_num_args() > 0) {
            $this->_size = $size;
            return $this;
        }
        return $this->_size;
    }

    public function types($types = null)
    {
        if (func_num_args() > 0) {
            $this->_types = $types;
            return $this;
        }
        return $this->_types;
    }

    public function readable($readbale = null)
    {
        if (func_num_args() > 0) {
            $this->_readbale = $readbale;
            return $this;
        }
        return $this->_readbale;
    }

    public function writable($writable = null)
    {
        if (func_num_args() > 0) {
            $this->_writable = $writable;
            return $this;
        }
        return $this->_writable;
    }

    protected function _validate($value)
    {
        if (!$value instanceof CoastFile) {
            $this->error(self::VALID);
            return;
        }
        if (!$value->exists()) {
            $this->error(self::EXISTS);
            return;
        }
        
        if (isset($this->_size) && $value->size() > $this->_size) {
            $this->error(self::SIZE);
        }
        if (isset($this->_types) && !in_array($value->extName(), $this->_types)) {
            $this->error(self::TYPE);
        }
        if ($this->_readable && $value->isReadable()) {
            $this->error(self::READABLE);
        }
        if ($this->_writable && $value->isWritable()) {
            $this->error(self::WRITABLE);
        }
    }
}
