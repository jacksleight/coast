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
     * Full path value.
     * @var string
     */
    protected $_value;

    /**
     * Constructs a new path object.
     * @param string $name Full path name.
     */
    public function __construct($value)
    {
        $this->value($value);
    }

    /**
     * Set the value.
     * @param string $name Full path name.
     */
    public function value($value = null)
    {
        if (isset($value)) {
            $value = str_replace('\\', '/', $value);
            $value = preg_replace('/\/+/', '/', $value);
            $this->_value = $value;
        }
        return $this->_value;
    }

    /**
     * Get full path name or part.
     * @param  string $part The part to return.
     * @return string
     */ 
    public function toString()
    {
        return $this->value();
    }

    /**
     * Get the directory name.
     * @return string
     */
    public function dirName()
    {
        return pathinfo($this->_value, PATHINFO_DIRNAME);
    }

    /**
     * Get the base name.
     * @return string
     */
    public function baseName()
    {
        return pathinfo($this->_value, PATHINFO_BASENAME);
    }

    /**
     * Get the extension name.
     * @return string
     */
    public function extName()
    {
        return pathinfo($this->_value, PATHINFO_EXTENSION);
    }

    /**
     * Get the file name.
     * @return string
     */
    public function fileName()
    {
        return pathinfo($this->_value, PATHINFO_FILENAME);
    }

    /**
     * Aliases `name`
     * @return string
     */
    public function __toString()
    {
        return $this->_value;
    }

    /**
     * Check if path is within another
     * @param  Coast\Path $target path to check against. 
     * @return bool
     */
    public function isWithin(\Coast\Path $parent)
    {
        $path = $this->_value;
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
        $target = explode('/', $this->_value);
        
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
        $target = explode('/', $this->_value);

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
        return substr($this->_value, 0, 1) == '/';
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