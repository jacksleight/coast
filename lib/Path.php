<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\File;
use Coast\Dir;

/**
 * Path object.
 */
class Path
{
    /**
     * Full path name.
     * @var string
     */
    protected $_name;

    /**
     * Constructs a new path object.
     * @param string $name Full path name.
     */
    public function __construct($name)
    {
        $this->name($name);
    }

    /**
     * Get/set the name.
     * @param string $name Full path name.
     */
    public function name($name = null)
    {
        if (func_num_args() > 0) {
            $this->_name = str_replace(DIRECTORY_SEPARATOR, '/', $name);
        }
        return $this->_name;
    }

    /**
     * Aliases `name`
     * @return string
     */
    public function __toString()
    {
        return $this->name();
    }

    /**
     * Get/set the directory name.
     * @return string
     */
    public function dirName($dirName = null)
    {
        if (func_num_args() > 0) {
            $parts = pathinfo($this->_name);
            $this->_name = "{$dirName}/{$parts['basename']}";
            return $this;
        }
        return pathinfo($this->_name, PATHINFO_DIRNAME);
    }

    /**
     * Get/set the base name.
     * @return string
     */
    public function baseName($baseName = null)
    {
        if (func_num_args() > 0) {
            $parts = pathinfo($this->_name);
            $this->_name = "{$parts['dirname']}/{$baseName}";
            return $this;
        }
        return pathinfo($this->_name, PATHINFO_BASENAME);
    }

    /**
     * Get/set the file name.
     * @return string
     */
    public function fileName($fileName = null)
    {
        if (func_num_args() > 0) {
            $parts = pathinfo($this->_name);
            $this->_name = "{$parts['dirname']}/{$fileName}.{$parts['extension']}";
            return $this;
        }
        return pathinfo($this->_name, PATHINFO_FILENAME);
    }

    /**
     * Get/set the extension name.
     * @return string
     */
    public function extName($extName = null)
    {
        if (func_num_args() > 0) {
            $parts = pathinfo($this->_name);
            $this->_name = "{$parts['dirname']}/{$parts['filename']}.{$extName}";
            return $this;
        }
        return pathinfo($this->_name, PATHINFO_EXTENSION);
    }

    /**
     * Add prefix.
     * @return string
     */
    public function prefix($prefix)
    {
        $parts = pathinfo($this->_name);
        $this->_name = "{$parts['dirname']}/{$prefix}{$parts['filename']}.{$parts['extension']}";
        return $this;
    }

    /**
     * Add suffix.
     * @return string
     */
    public function suffix($suffix)
    {
        $parts = pathinfo($this->_name);
        $this->_name = "{$parts['dirname']}/{$parts['filename']}{$suffix}.{$parts['extension']}";
        return $this;
    }

    /**
     * Check if path is within another
     * @param  Coast\Path $target path to check against. 
     * @return bool
     */
    public function isWithin(\Coast\Path $parent)
    {
        $path = $this->_name;
        $parts = \explode(PATH_SEPARATOR, (string) $parent);    
        foreach ($parts as $part) {
            if (\preg_match('/^' . \preg_quote($part, '/') . '/', $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get absolute path from relative path.
     * @param  Coast\Path $base base absolute path.
     * @return Coast\Path
     */
    public function toAbsolute(\Coast\Path $base)
    {
        if (!$this->isRelative() || !$base->isAbsolute()) {
            throw new \Exception("Path '{$this}' is not relative or base path '{$base}' is not absolute");
        }

        $source = explode('/', (string) $base);
        $target = explode('/', $this->_name);
        
        $name = $source;
        array_pop($name);
        foreach ($target as $part) {
            if ($part == '..') {
                array_pop($name);
            } else if ($part != '.' && $part != '') {
                $name[] = $part;
            }
        }
        $name = implode('/', $name);

        $class = get_class($this);
        return new $class($name);
    }

    /**
     * Get relative path from absolute path.
     * @param  Coast\Path $base Base absolute path.
     * @return Coast\Path
     */
    public function toRelative(\Coast\Path $base)
    {
        if (!$this->isAbsolute() || !$base->isAbsolute()) {
            throw new \Exception("Source path '{$this}' is not absolute or base path '{$base}' is not absolute");
        }
        
        $source = explode('/', (string) $base);
        $target = explode('/', $this->_name);

        $name = $target;
        foreach ($source as $i => $part) {
            if ($part == $target[$i]) {
                array_shift($name);
            } else {
                $name = array_pad($name, (count($name) + (count($source) - $i) - 1) * -1, '..');
                break;
            }
        }
        $name = implode('/', $name);

        $class = get_class($this);
        return new $class($name);
    }

    /**
     * Reduce absolute path with relative parts to real path.
     * @return Coast\Path
     */
    public function toReal()
    {
        if (!$this->isAbsolute()) {
            throw new \Exception("Path '{$this}' is not absolute");
        }

        $target = explode('/', $this->_name);
        
        $name = [];
        foreach ($target as $part) {
            if ($part == '..') {
                array_pop($name);
            } else if ($part != '.') {
                $name[] = $part;
            }
        }
        $name = implode('/', $name);

        $class = get_class($this);
        return new $class($name);
    }

    /**
     * Is path absolute.
     * @return bool
     */
    public function isAbsolute()
    {
        return substr($this->_name, 0, 1) == '/';
    }

    /**
     * Is path relative.
     * @return bool
     */
    public function isRelative()
    {
        return !$this->isAbsolute();
    }

    public function path($path)
    {
        $path = ltrim($path, '/');
        return new Path("{$this->_name}/{$path}");
    }

    public function toDir()
    {
        return new Dir($this->_name);
    }

    public function toFile()
    {
        return new File($this->_name);
    }

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