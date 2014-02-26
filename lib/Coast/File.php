<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class File extends \Coast\File\Path
{
    public static function tempoary()
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', tempnam(sys_get_temp_dir(), 'temp_'));
        if (!$path) {
            throw new \Exception('Could not create tempoary file');
        }
        return new \Coast\File($path);
    }
    
    public function open($mode = 'r', $class = 'Coast\File\Data')
    {
        return new $class($this->name(), $mode);
    }

    public function move(\Coast\Dir $dir, $name = null, $upload = false)
    {
        $path = $dir->name() . '/' . (isset($name)
            ? $name
            : $this->name(\Coast\Path::BASENAME));
        $upload
            ? move_uploaded_file($this->name(), $path)
            : rename($this->name(), $path);
        return new \Coast\File($path);
    }

    public function copy(\Coast\Dir $dir, $name = null)
    {
        $path = $dir->name() . '/' . (isset($name)
            ? $name
            : $this->name(\Coast\Path::BASENAME));
        copy($this->name(), $path);
        return new \Coast\File($path);
    }

    public function rename($name)
    {
        $path = $this->name(\Coast\Path::DIRNAME) . '/' . $name;
        rename($this->name(), $path);
        return new \Coast\File($path);
    }

    public function remove()
    {
        unlink($this->name());
        return $this;
    }

    public function permissions($mode = null)
    {
        if (isset($mode)) {
            chmod($this->name(), $mode);
            return $this;
        }
        return parent::permissions();
    }

    public function touch(\DateTime $modify = null, \DateTime $access = null)
    {
        touch($this->name(), $modify->getTimestamp(), $access->getTimestamp());
        return $this;
    }

    public function size()
    {
        return filesize($this->name());
    }

    public function hash($type)
    {
        return hash_file($type, $this->name());
    }

    public function dir($mode = null)
    {
        return new \Coast\Dir($this->name(\Coast\Path::DIRNAME), $mode);
    }

    public function accessedTime()
    {
        return (new \DateTime())->setTimestamp(fileatime($this->name()));
    }

    public function changedTime()
    {
        return (new \DateTime())->setTimestamp(filectime($this->name()));
    }

    public function modifiedTime()
    {
        return (new \DateTime())->setTimestamp(filemtime($this->name()));
    }
}