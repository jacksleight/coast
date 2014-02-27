<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class File extends \Coast\File\Path
{
    public static function createTempoary()
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', tempnam(sys_get_temp_dir(), 'php'));
        if (!$path) {
            throw new \Exception('Could not create tempoary file');
        }
        return new \Coast\File($path);
    }
    
    public function open($mode = 'r', $class = 'Coast\File\Data')
    {
        return new $class($this->_value, $mode);
    }

    public function move(\Coast\Dir $dir, $name = null, $upload = false)
    {
        $path = $dir->toString() . '/' . (isset($name)
            ? $name
            : $this->baseName());
        $upload
            ? move_uploaded_file($this->_value, $path)
            : rename($this->_value, $path);
        return new \Coast\File($path);
    }

    public function copy(\Coast\Dir $dir, $name = null)
    {
        $path = $dir->toString() . '/' . (isset($name)
            ? $name
            : $this->baseName());
        copy($this->_value, $path);
        return new \Coast\File($path);
    }

    public function rename($name)
    {
        $path = $this->dirName() . '/' . $name;
        rename($this->_value, $path);
        return new \Coast\File($path);
    }

    public function remove()
    {
        unlink($this->_value);
        return $this;
    }

    public function permissions($mode = null)
    {
        if (isset($mode)) {
            chmod($this->_value, $mode);
            return $this;
        }
        return parent::permissions();
    }

    public function touch(\DateTime $modify = null, \DateTime $access = null)
    {
        touch($this->_value, $modify->getTimestamp(), $access->getTimestamp());
        return $this;
    }

    public function size()
    {
        return filesize($this->_value);
    }

    public function hash($type)
    {
        return hash_file($type, $this->_value);
    }

    public function dir($mode = null)
    {
        return new \Coast\Dir($this->dirName(), $mode);
    }

    public function accessTime()
    {
        return (new \DateTime())->setTimestamp(fileatime($this->_value));
    }

    public function changeTime()
    {
        return (new \DateTime())->setTimestamp(filectime($this->_value));
    }

    public function modifyTime()
    {
        return (new \DateTime())->setTimestamp(filemtime($this->_value));
    }
}