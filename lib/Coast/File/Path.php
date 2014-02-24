<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\File;

abstract class Path extends \Coast\Path
{
    public function exists()
    {
        return file_exists($this->name());
    }

    public function isDir()
    {
        return is_dir($this->name());
    }

    public function isFile()
    {
        return is_file($this->name());
    }

    public function isReadable()
    {
        return is_readable($this->name());
    }

    public function isWritable()
    {
        return is_writable($this->name());
    }

    public function permissions()
    {
        return substr(sprintf('%o', fileperms($this->name())), -4);
    }
    
    abstract public function remove();
}