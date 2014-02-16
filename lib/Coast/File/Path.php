<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\File;

abstract class Path extends \Coast\Path
{
    const TYPE_DIR  = 'dir';
    const TYPE_FILE = 'file';

    public function exists()
    {
        return file_exists($this->name());
    }

    public function type()
    {
        return is_dir($this->name()) ? self::TYPE_DIR : self::TYPE_FILE;
    }

    public function readable()
    {
        return is_readable($this->name());
    }

    public function writable()
    {
        return is_writable($this->name());
    }

    public function permissions()
    {
        return substr(sprintf('%o', fileperms($this->name())), -4);
    }
    
    abstract public function remove();
}