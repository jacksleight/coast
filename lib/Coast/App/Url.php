<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class Url implements \Coast\App\Access
{
    use \Coast\App\Access\Implementation;
    use \Coast\Options;

    public function __construct(array $options = array())
    {
        $this->options(array_merge([
            'base'    => '/',
            'dir'     => '',
            'cdnBase' => null,
            'version' => false,
            'router'  => null,
        ], $options));
    }

    protected function _initialize($name, $value)
    {
        switch ($name) {
            case 'base':
            case 'cdnBase':
                $value = new \Coast\Url("{$value}");
                break;
            case 'dir':
                $value = new \Coast\Dir("{$value}");
                $value = $value->relative()
                    ? new \Coast\Dir(getcwd() . "/{$value}")
                    : $value;
                break;
        }
        return $value;
    }

    public function call()
    {
        $args = func_get_args();
        if (!isset($args[0])) {
            $method = 'base';
        } else if (is_array($args[0])) {
            $method = 'route';
        } else if ($args[0] instanceof \Coast\Url) {
            $method = 'url';
        } else if ($args[0] instanceof \Coast\Dir) {
            $method = 'dir';
        } else if ($args[0] instanceof \Coast\File) {
            $method = 'file';
        } else if ($args[0] instanceof \Coast\Path) {
            $method = 'path';
        } else {
            $method = 'string';
        }
        return call_user_func_array(array($this, $method), $args);
    }

    public function base()
    {
        return $this->_options->base->name();
    }

    public function string($string, $base = true)
    {
        $path = (string) $string;
        return $base
            ? $this->_options->base . $path
            : $path;
    }

    public function route(array $params = array(), $name = null, $reset = false, $base = true)
    {
        if (!isset($this->_options->router)) {
            throw new \Coast\App\Exception("Router option has not been set");
        }
        $route = isset($this->req)
            ? $this->req->param('route')
            : null;
        if (!isset($name)) {
            if (!isset($route)) {
                throw new \Coast\App\Exception("Route not specified and no previous route is avaliable");
            }
            $name = $route['name'];
        }
        if (!$reset && isset($route)) {
            $params = array_merge(
                $route['params'],
                $params
            );
        }
        $path = ltrim($this->_options->router->reverse($name, $params), '/');
        return $base
            ? $this->_options->base . $path
            : $path;
    }

    public function url(\Coast\Url $url, $to = null, $start = false)
    {
        return $url->name($to, $start);
    }

    public function dir($dir, $base = true, $cdn = true)
    {
        $dir = !$dir instanceof \Coast\Dir
            ? new \Coast\Dir("{$dir}")
            : $dir;
        return $this->path($dir, $base, $cdn);
    }

    public function file($file, $base = true, $cdn = true)
    {
        $file = !$file instanceof \Coast\File
            ? new \Coast\File("{$file}")
            : $file;
        return $this->path($file, $base, $cdn);
    }

    public function path($path, $base = true, $cdn = true)
    {
        $path = !$path instanceof \Coast\Path
            ? new \Coast\Path("{$path}")
            : $path;
        $class = get_class($path);
        $path = $path->relative()
            ? new $class(getcwd() . "/{$path}")
            : $path;
        if (!$path->within($this->_options->dir)) {
            throw new \Coast\App\Exception("Path '{$path}' is not within base directory");
        }

        if ($this->_options->version && $path instanceof \Coast\File && $path->exists()) {
            $time     = $path->modify()->getTimestamp();
            $dirname  = $path->dirname();
            $filename = $path->filename();
            $extname  = $path->extname();
            $dirname = $dirname != '.'
                ? "{$dirname}/"
                : '';
            $path = new \Coast\File("{$dirname}{$filename}.{$time}.{$extname}");
        }

        $path = $this->_options->dir->to($path);
        if ($base) {
            $path = $cdn && isset($this->_options->cdnBase)
                ? $this->_options->cdnBase . $path
                : $this->_options->base . $path;
        }
        return $path;
    }

    public function query(array $params = array(), $reset = false, $mark = true)
    {
        $params = $this->_parseQueryParams($params, $reset);
        $query  = array();
        foreach ($params as $name => $value) {
            $query[] = $name . '=' . urlencode($value);
        }
        $query = implode('&', $query);
        
        return $mark
            ? '?' . $query
            : $query;
    }

    public function inputs(array $params = array(), $reset = false)
    {
        $params = $this->_parseQueryParams($params, $reset);
        $inputs = array();
        foreach ($params as $name => $value) {
            $inputs[] = '<input type="hidden" name="' . $name . '" value="' . $value . '">';
        }
        
        return implode($inputs);
    }

    protected function _parseQueryParams(array $params = array(), $reset = false)
    {
        if (!$reset && isset($this->req)) {
            $params = \Coast\array_merge_smart(
                $this->req->queryParams(),
                $params
            );
        }
        $params = \Coast\array_filter_null_recursive($params);
        
        return $params;
    }
}