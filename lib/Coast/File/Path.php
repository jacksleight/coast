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

    public function move(\Coast\Dir $dir, $baseName = null)
    {
        $name = "{$dir}/" . (isset($baseName)
            ? $baseName
            : $this->baseName());
        rename($this->_name, $name);
        $this->_name = $name;
        return $this;
    }

    public function rename($baseName, \Coast\Dir $dir = null)
    {
        $name = (isset($dir)
            ? $dir
            : $this->dir()) . "/{$baseName}";
        rename($this->_name, $name);
        $this->_name = $name;
        return $this;
    }

    abstract public function copy(\Coast\Dir $dir, $baseName = null);
    
    abstract public function remove();
}