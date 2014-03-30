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
            $this->_data = array_merge_recursive(
                $this->_data,
                require (string) $file
            );
        }
    }

    /**
     * Get a param.
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        return isset($this->_data[$name])
            ? $this->_data[$name]
            : null;
    }

    /**
     * Check if a param is set.
     * @param  string  $name
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * Alias of `get`
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }
    
    /**
     * Alias of `has`
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }
}