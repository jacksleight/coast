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
    const DIRNAME  = PATHINFO_DIRNAME;
    const BASENAME = PATHINFO_BASENAME;
    const EXTNAME  = PATHINFO_EXTENSION;
    const FILENAME = PATHINFO_FILENAME;

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
        $name = str_replace('\\', '/', $name);
        $name = preg_replace('/\/+/', '/', $name);
        $name = $name != '/'
            ? rtrim($name, '/')
            : $name;
        $this->_name = $name;
    }

    /**
     * Get full path name or part.
     * @param  string $part The part to return.
     * @return string
     */ 
    public function name($part = null)
    {
        return isset($part)
            ? pathinfo($this->_name, $part)
            : $this->_name;
    }

    /**
     * Get the directory name.
     * @return string
     */
    public function dirname()
    {
        return $this->name(self::DIRNAME);
    }

    /**
     * Get the base name.
     * @return string
     */
    public function basename()
    {
        return $this->name(self::BASENAME);
    }

    /**
     * Get the extension name.
     * @return string
     */
    public function extname()
    {
        return $this->name(self::EXTNAME);
    }

    /**
     * Get the file name.
     * @return string
     */
    public function filename()
    {
        return $this->name(self::FILENAME);
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
     * Check if path is within another
     * @param  Coast\Path $target path to check against. 
     * @return bool
     */
    public function within(\Coast\Path $parent)
    {
        $path = $this->name();
        $parts = \explode(PATH_SEPARATOR, $parent->name());    
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
     * @todo   Review method name of resolve and unresolve.
     */
    public function resolve(\Coast\Path $base)
    {
        if (!$this->relative() || !$base->absolute()) {
            throw new \Exception("Path '" . $this->name() . "' is not relative or base path '" . $base->name() . "' is not absolute");
        }

        $source = explode('/', $base->name());
        $target = explode('/', $this->name());
        
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
     * @todo   Review method name of resolve and unresolve.
     */
    public function unresolve(\Coast\Path $base)
    {
        if (!$this->absolute() || !$base->absolute()) {
            throw new \Exception("Source path '" . $this->name() . "' is not absolute or base path '" . $base->name() . "' is not absolute");
        }
        
        $source = explode('/', $base->name());
        $target = explode('/', $this->name());

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
    public function absolute()
    {
        return substr($this->name(), 0, 1) == '/';
    }

    /**
     * Is path relative.
     * @return bool
     */
    public function relative()
    {
        return !$this->absolute();
    }
}