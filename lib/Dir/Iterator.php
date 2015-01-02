<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Dir;

class Iterator implements \SeekableIterator
{
    protected $_spl;

    public function __construct($path, $flags = null, $recursive = false, $mode = null)
    {
        $flags = !isset($flags)
            ? \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS
            : $flags;
        $this->_spl = !$recursive
            ? new \FilesystemIterator($path, $flags)
            : new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, $flags), $mode);
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_spl, $method), $args);
    }

    public function current()
    {
        $path = $this->_spl->current()->getPathname();
        return $this->_spl->isDir()
            ? new \Coast\Dir($path)
            : new \Coast\File($path);
    }

    public function key()
    {
        return $this->_spl->key();
    }

    public function next()
    {
        return $this->_spl->next();
    }

    public function rewind()
    {
        return $this->_spl->rewind();
    }

    public function valid()
    {
        return $this->_spl->valid();
    }

    public function seek($position)
    {
        return $this->_spl->seek($position);
    }
}