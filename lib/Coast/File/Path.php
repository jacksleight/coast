<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\File;

abstract class Path extends \Coast\Path
{
    /**
     * @todo isReal?
     */
    public function exists()
    {
        return file_exists($this->_name);
    }

    public function isDir()
    {
        return is_dir($this->_name);
    }

    public function isFile()
    {
        return is_file($this->_name);
    }

    public function isReadable()
    {
        return is_readable($this->_name);
    }

    public function isWritable()
    {
        return is_writable($this->_name);
    }

    public function permissions()
    {
        return substr(sprintf('%o', fileperms($this->_name)), -4);
    }
    
    abstract public function remove();
}