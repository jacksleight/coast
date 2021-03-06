<?php
/* 
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\File;

/**
 * PHP file based config object.
 */
class Config
{
    /**
     * Config opts.
     * @var array
     */
    protected $_opts = [];

    /**
     * Construct a new config object.
     * @param array $files List of PHP files to parse.
     */
    public function __construct($files = array())
    {
        $this->load($files);
    }

    /**
     * Load files.
     * @param  string $name
     * @return mixed
     */
    public function load($files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }
        foreach ($files as $file) {
            $file = new File($file);
            $extName = $file->extName();
            if ($extName == 'json') {
                $data = json_decode($file->readAll(), true);
                if (!$data) {
                    throw new \Exception("File '{$file}' is not valid JSON");
                }
            } else {
                $data = require (string) $file;
            }
            $this->fromArray($data);
        }
    }

    /**
     * Import from an array.
     * @param  string $name
     * @return mixed
     */
    public function fromArray(array $opts) 
    {
        $this->_opts = \Coast\array_merge_smart(
            $this->_opts,
            $opts
        );
        return $this;
    }

    public function opt($name, $value = null)
    {
        if (func_num_args() > 1) {
            $this->_opts[$name] = $value;
            return $this;
        }
        return isset($this->_opts[$name])
            ? $this->_opts[$name]
            : null;
    }

    public function opts(array $opts = null)
    {
        if (func_num_args() > 0) {
            foreach ($opts as $name => $value) {
                $this->opt($name, $value);
            }
            return $this;
        }
        return $this->_opts;
    }

    public function __get($name)
    {
        return $this->opt($name);
    }

    public function __isset($name)
    {
        return $this->opt($name) !== null;
    }
}