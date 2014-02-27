<?php
/* 
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Dir extends \Coast\File\Path implements \IteratorAggregate
{
    public function __construct($path, $mode = null)
    {
        parent::__construct($path);
        if (isset($mode)) {
            $this->create($mode);
        }
    }

    public function iterator($recursive = false, $mode = \RecursiveIteratorIterator::LEAVES_ONLY, $flags = 0)
    {
        return new \Coast\Dir\Iterator($this->_value, $recursive, $mode, $flags);
    }

    public function create($mode = null)
    {
        $stack = explode('/', $this->_value);
        $parts = [];
        while (count($stack) > 0) {
            array_push($parts, array_shift($stack));
            $create = implode('/', $parts);
            if (strlen($create) == 0) {
                continue;
            }
            if (!is_dir($create)) {
                if (mkdir($create) && isset($mode)) {
                    chmod($create, $mode);
                }
            }
        }
        return $this;
    }

    public function remove($recursive = false)
    {
        if ($recursive) {
            foreach ($this->iterator(null, true, \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                $path->remove();
            }
        }
        rmdir($this->_value);
        return $this;
    }

    public function permissions($mode = null, $recursive = false)
    {
        if (isset($mode)) {
            if ($recursive) {
                foreach ($this->iterator(null, true, \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                    $path->chmod($mode);
                }
            }
            chmod($this->_value, $mode);
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
        return new \Coast\File("{$this->_value}/{$path}");
    }

    public function dir($path, $mode = null)
    {
        return new \Coast\Dir("{$this->_value}/{$path}", $mode);
    }

    public function getIterator()
    {
        return $this->iterator();
    }
}