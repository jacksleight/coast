<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Path
{
    const ALL       = 0;
    const DIRNAME   = PATHINFO_DIRNAME;
    const BASENAME  = PATHINFO_BASENAME;
    const EXTENSION = PATHINFO_EXTENSION;
    const FILENAME  = PATHINFO_FILENAME;

    protected $_name;

    public function __construct($name)
    {
        $name = str_replace('\\', '/', $name);
        $name = preg_replace('/\/+/', '/', $name);
        $name = $name != '/'
            ? rtrim($name, '/')
            : $name;
        $this->_name = $name;
    }

    public function string($part = null)
    {
        return isset($part)
            ? ($part == self::ALL ? pathinfo($this->_name) : pathinfo($this->_name, $part))
            : $this->_name;
    }

    public function __toString()
    {
        return $this->string();
    }

    public function within(\Coast\Path $target)
    {
        $path = $this->string();
        $parts = \explode(PATH_SEPARATOR, $target->string());    
        foreach ($parts as $part) {
            if (\preg_match('/^' . \preg_quote($part, '/') . '/', $path)) {
                return true;
            }
        }
        return false;
    }

    public function from(\Coast\Path $target)
    {
        if (!$this->absolute() || !$target->relative()) {
            throw new \Exception("Source path '" . $this->string() . "' is not absolute or target path '" . $target->string() . "' is not relative");
        }

        $source = explode('/', $this->string());
        $target = explode('/', $target->string());
        
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

    public function to(\Coast\Path $target)
    {
        if (!$this->absolute() || !$target->absolute()) {
            throw new \Exception("Source path '" . $this->string() . "' is not absolute or target path '" . $target->string() . "' is not absolute");
        }
        
        $source = explode('/', $this->string());
        $target = explode('/', $target->string());

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

    public function absolute()
    {
        return substr($this->string(), 0, 1) == '/';
    }

    public function relative()
    {
        return !$this->absolute();
    }
}