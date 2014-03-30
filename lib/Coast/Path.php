<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

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
    public function __construct($value)
    {
        $this->name($value);
    }

    /**
     * Get/set the name.
     * @param string $name Full path name.
     */
    public function name($value = null)
    {
        if (isset($value)) {
            $this->_name = str_replace(DIRECTORY_SEPARATOR, '/', $value);
        }
        return $this->_name;
    }

    /**
     * Aliases `name`
     * @return string
     */ 
    public function toString()
    {
        return $this->name();
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
    public function dirName($value = null)
    {
        if (isset($value)) {
            $parts = pathinfo($this->_name);
            $this->_name = "{$value}/{$parts['basename']}";
            return $this;
        }
        return pathinfo($this->_name, PATHINFO_DIRNAME);
    }

    /**
     * Get/set the base name.
     * @return string
     */
    public function baseName($value = null)
    {
        if (isset($value)) {
            $parts = pathinfo($this->_name);
            $this->_name = "{$parts['dirname']}/{$value}";
            return $this;
        }
        return pathinfo($this->_name, PATHINFO_BASENAME);
    }

    /**
     * Get/set the file name.
     * @return string
     */
    public function fileName($value = null)
    {
        if (isset($value)) {
            $parts = pathinfo($this->_name);
            $this->_name = "{$parts['dirname']}/{$value}.{$parts['extension']}";
            return $this;
        }
        return pathinfo($this->_name, PATHINFO_FILENAME);
    }

    /**
     * Get/set the extension name.
     * @return string
     */
    public function extName($value = null)
    {
        if (isset($value)) {
            $parts = pathinfo($this->_name);
            $this->_name = "{$parts['dirname']}/{$parts['filename']}.{$value}";
            return $this;
        }
        return pathinfo($this->_name, PATHINFO_EXTENSION);
    }

    /**
     * Add prefix.
     * @return string
     */
    public function prefix($value)
    {
        $parts = pathinfo($this->_name);
        $this->_name = "{$parts['dirname']}/{$value}{$parts['filename']}.{$parts['extension']}";
        return $this;
    }

    /**
     * Add suffix.
     * @return string
     */
    public function suffix($value)
    {
        $parts = pathinfo($this->_name);
        $this->_name = "{$parts['dirname']}/{$parts['filename']}{$value}.{$parts['extension']}";
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
        $parts = \explode(PATH_SEPARATOR, $parent->toString());    
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

        $source = explode('/', $base->toString());
        $target = explode('/', $this->_name);
        
        $name = $source;
        array_pop($name);
        foreach ($target as $part) {
            if ($part == '..') {
                array_pop($name);
            } elseif ($part != '.' && $part != '') {
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
        
        $source = explode('/', $base->toString());
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
}