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
     * Path name.
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
     * @param  \Coast\Path $target path to check against. 
     * @return bool
     */
    public function within(\Coast\Path $target)
    {
        $path = $this->name();
        $parts = \explode(PATH_SEPARATOR, $target->name());    
        foreach ($parts as $part) {
            if (\preg_match('/^' . \preg_quote($part, '/') . '/', $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Resolve absolute path from relative path.
     * @param  \Coast\Path $target Target relative path.
     * @return \Coast\Path
     * @todo   Rename to resolve, allow opposite order. 
     */
    public function from(\Coast\Path $target)
    {
        if (!$this->absolute() || !$target->relative()) {
            throw new \Exception("Source path '" . $this->name() . "' is not absolute or target path '" . $target->name() . "' is not relative");
        }

        $source = explode('/', $this->name());
        $target = explode('/', $target->name());
        
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
     * Get relative path from two absolute paths.
     * @param  \Coast\Path $target Target path.
     * @return \Coast\Path
     * @todo   Rename.
     */
    public function to(\Coast\Path $target)
    {
        if (!$this->absolute() || !$target->absolute()) {
            throw new \Exception("Source path '" . $this->name() . "' is not absolute or target path '" . $target->name() . "' is not absolute");
        }
        
        $source = explode('/', $this->name());
        $target = explode('/', $target->name());

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