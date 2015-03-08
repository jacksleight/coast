<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class Upload extends Rule
{
    const VALID = 'valid';
    const ERROR = 'error';
    const SIZE  = 'size';
    const TYPE  = 'type';

    protected $_size  = null;
    protected $_types = null;

    public function __construct($size = null, $types = null)
    {
        $this->size($size);
        $this->types($types);
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

    protected function _validate($value)
    {
        if (!$this->_validateArray($value)) {
            $this->error(self::VALID);
            return;
        }
        if ($value['error'] != UPLOAD_ERR_OK) {
            switch ($value['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $this->error(self::SIZE);
                    break;
                case UPLOAD_ERR_PARTIAL:
                case UPLOAD_ERR_NO_FILE:
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                case UPLOAD_ERR_EXTENSION:
                default:
                    $this->error(self::ERROR);
                    break;
            }
            return;
        }

        if (isset($this->_size) && $value['size'] > $this->_size) {
            $this->error(self::SIZE);
        }
        if (isset($this->_types) && !in_array(pathinfo((string) $value['name'], PATHINFO_EXTENSION), $this->_types)) {
            $this->error(self::TYPE);
        }
    }

    protected function _validateArray($value)
    {
        if (!is_array($value)) {
            return false;
        }
        return array_keys($value) == [
            'name',
            'type',
            'tmp_name',
            'error',
            'size',
        ];
    }
}
