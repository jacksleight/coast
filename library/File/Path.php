<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\File;

use DateTime;

abstract class Path extends \Coast\Path
{
    public function exists()
    {
        return file_exists($this->_name);
    }

    public function isExist()
    {
        return $this->exists();
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
            ? $this->_parseBaseName($baseName)
            : $this->baseName());
        rename($this->_name, $name);
        $this->_name = $name;
        return $this;
    }

    public function rename($baseName, \Coast\Dir $dir = null)
    {
        $name = (isset($dir)
            ? $dir
            : $this->dir()) . "/{$this->_parseBaseName($baseName)}";
        rename($this->_name, $name);
        $this->_name = $name;
        return $this;
    }

    abstract public function copy(\Coast\Dir $dir, $baseName = null);
    
    abstract public function remove();

    public function accessTime()
    {
        return (new DateTime())->setTimestamp(fileatime($this->_name));
    }

    public function changeTime()
    {
        return (new DateTime())->setTimestamp(filectime($this->_name));
    }

    public function modifyTime()
    {
        return (new DateTime())->setTimestamp(filemtime($this->_name));
    }

    protected function _parseBaseName($baseName)
    {
        if (is_array($baseName)) {
            $baseName = array_intersect_key($baseName, [
                'baseName' => null,
                'fileName' => null,
                'extName'  => null,
                'prefix'   => null,
                'suffix'   => null,
            ]);
            $path = new \Coast\Path($this->baseName());
            foreach ($baseName as $method => $value) {
                $path->$method($value);
            }
            $baseName = $path->baseName();
        }
        return $baseName;
    }
}