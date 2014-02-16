<?php
/* 
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

/**
 * PHP file based config object.
 */
class Config
{
    /**
     * Config data.
     * @var array
     */
    protected $_data = [];

    /**
     * Consutruct a new config object.
     * @param array $files List of PHP files to parse.
     */
    public function __construct($files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }
        foreach ($files as $file) {
            $data = [];
            require (string) $file;
            $this->_data = array_merge_recursive(
                $this->_data,
                $data
            );
        }
    }

    /**
     * Set a param.
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * Get a param.
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->_data[$name])
            ? $this->_data[$name]
            : null;
    }

    /**
     * Unset a param.
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->_data[$name]);
    }

    /**
     * Check if a param is set.
     * @param  string  $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }
}