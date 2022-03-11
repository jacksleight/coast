<?php
/* 
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\File;

class Dir extends \Coast\File\Path implements \IteratorAggregate
{
    public static function createTemp()
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'php' . uniqid());
        if (!$path) {
            throw new \Exception('Could not create tempoary directory');
        }
        return new \Coast\Dir($path, true);
    }

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
        return new File("{$this->_name}/{$path}");
    }

    public function dir($path, $create = false)
    {
        $path = ltrim($path, '/');
        return new Dir(strlen($path) ? "{$this->_name}/{$path}" : $this->_name, $create);
    }

    public function glob($glob, $flags = 0)
    {
        $paths = glob("{$this->_name}/{$glob}", $flags);
        $files = array_map(function($v) { return new File($v); }, $paths);
        return $files;
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return $this->iterator();
    }
}