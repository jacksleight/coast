<?php
/* 
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Dir extends \Coast\File\Path implements \IteratorAggregate
{
    public function __construct($path, $create = false)
    {
        parent::__construct($path);
        if ($create && !$this->exists()) {
            $this->create($create);
        }
    }

    public function iterator($flags = null, $recursive = false, $mode = null)
    {
        return new \Coast\Dir\Iterator($this->_name, $flags, $recursive, $mode);
    }

    public function create()
    {
        $umask = umask(0);
        mkdir($this->_name, 0777, true);
        umask($umask);
        return $this;
    }

    public function copy(\Coast\Dir $dir, $baseName = null, $recursive = false)
    {
        $name = "{$dir}/" . (isset($baseName)
            ? $this->_parseBaseName($baseName)
            : $this->baseName());
        $umask = umask(0);
        mkdir($name, 0777, true);
        if ($recursive) {
            foreach ($this->iterator(null, true, \RecursiveIteratorIterator::SELF_FIRST) as $child) {
                $copy = "{$name}/{$child->toRelative($this)}";
                $child->isDir()
                    ? mkdir($copy, 0777, true)
                    : copy($child->name(), $copy);
            }
        }
        umask($umask);
        return new \Coast\Dir($name);
    }

    public function remove($recursive = false)
    {
        if ($recursive) {
            foreach ($this->iterator(null, true, \RecursiveIteratorIterator::CHILD_FIRST) as $child) {
                $child->remove();
            }
        }
        rmdir($this->_name);
        return $this;
    }

    public function permissions($mode = null, $recursive = false)
    {
        if (isset($mode)) {
            if ($recursive) {
                foreach ($this->iterator(null, true, \RecursiveIteratorIterator::CHILD_FIRST) as $child) {
                    $child->chmod($mode);
                }
            }
            chmod($this->_name, $mode);
            return $this;
        }
        return parent::permissions();
    }

    public function size($recursive = false)
    {
        $size = 0;
        foreach ($this->iterator(null, $recursive) as $path) {
            if (!$path->exists()) {
                continue;
            }
            $size += $path->size();
        }
        return $size;
    }

    public function file($path)
    {
        $path = ltrim($path, '/');
        return new \Coast\File("{$this->_name}/{$path}");
    }

    public function dir($path, $create = false)
    {
        $path = ltrim($path, '/');
        return new \Coast\Dir(strlen($path) ? "{$this->_name}/{$path}" : $this->_name, $create);
    }

    public function getIterator()
    {
        return $this->iterator();
    }
}