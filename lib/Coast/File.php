<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class File extends \Coast\File\Path
{
    protected $_chars = [',', '"', '\\'];
    protected $_handle;

    public static function createTemp()
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', tempnam(sys_get_temp_dir(), 'php'));
        if (!$path) {
            throw new \Exception('Could not create tempoary file');
        }
        return new \Coast\File($path);
    }

    public function chars($delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $this->_chars = [$delimiter, $enclosure, $escape];
        return $this;
    }
    
    public function open($mode = 'r')
    {
        $this->_handle = fopen($this->_value, $mode);
        return $this;
    }

    public function close()
    {
        if (!isset($this->_handle)) {
            throw new \Exception("File '{$this}' is not open");
        }
        fclose($this->_handle);
        $this->_handle = null;
        return $this;
    }

    public function read($length = null)
    {
        if (!isset($this->_handle)) {
            throw new \Exception("File '{$this}' is not open");
        }
        $size = $this->size();
        return isset($length)
            ? fread($this->_handle, $length)
            : fread($this->_handle, $size ? $size : 1);
    }

    public function write($string, $length = null)
    {
        if (!isset($this->_handle)) {
            throw new \Exception("File '{$this}' is not open");
        }
        isset($length)
            ? fwrite($this->_handle, $string, $length)
            : fwrite($this->_handle, $string);
        return $this;
    }

    public function get($length = null)
    {
        if (!isset($this->_handle)) {
            throw new \Exception("File '{$this}' is not open");
        }
        return isset($length)
            ? fgets($this->_handle, $length)
            : fgets($this->_handle);
    }

    public function put($string, $length = null)
    {
        if (!isset($this->_handle)) {
            throw new \Exception("File '{$this}' is not open");
        }
        $this->write($string, $length) . $this->write("\n");
        return $this;
    }

    public function getCsv($length = 0)
    {
        if (!isset($this->_handle)) {
            throw new \Exception("File '{$this}' is not open");
        }
        return fgetcsv($this->_handle, $length, $this->_chars[0], $this->_chars[1], $this->_chars[2]);
    }

    public function putCsv($array)
    {
        if (!isset($this->_handle)) {
            throw new \Exception("File '{$this}' is not open");
        }
        fputcsv($this->_handle, $array, $this->_chars[0], $this->_chars[1]);
        return $this;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if (!isset($this->_handle)) {
            throw new \Exception("File '{$this}' is not open");
        }
        fseek($this->_handle, $offset, $whence);
        return $this;
    }

    public function truncate($length = 0)
    {
        if (!isset($this->_handle)) {
            throw new \Exception("File '{$this}' is not open");
        }
        ftruncate($this->_handle, $length);
        return $this;
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