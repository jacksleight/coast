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

    public function __construct(array $opts = array())
    {
        $this->opts(array_merge([
            'base'        => '/',
            'dir'         => getcwd(),
            'dirBase'     => null,
            'cdnBase'     => null,
            'router'      => null,
            'isVersioned' => true,
        ], $opts));
    }

    protected function _optInit($name, $value)
    {
        switch ($name) {
            case 'base':
            case 'dirBase':
            case 'cdnBase':
                $value = new \Coast\Url("{$value}");
                break;
            case 'dir':
                $value = !$value instanceof Dir
                    ? new \Coast\Dir("{$value}")
                    : $value;
                $value = $value->isRelative()
                    ? $this->app->dir()->dir($value)
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
        return $this->_opts->base;
    }

    public function dirBase()
    {
        return isset($this->_opts->dirBase)
            ? $this->_opts->dirBase
            : $this->_opts->base;
    }

    public function cdnBase()
    {
        return isset($this->_opts->cdnBase)
            ? $this->_opts->cdnBase
            : $this->dirBase();
    }

    public function string($string, $base = true)
    {
        $path = (string) $string;
        return new \Coast\Url($base
            ? $this->_opts->base . $path
            : $path);
    }

    public function route(array $params = array(), $name = null, $reset = false, $base = true)
    {
        if (!isset($this->_opts->router)) {
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
        $path = ltrim($this->_opts->router->reverse($name, $params), '/');
        return new \Coast\Url($base
            ? $this->_opts->base . $path
            : $path);
    }

    public function url(\Coast\Url $url)
    {
        return $url;
    }

    public function dir($dir, $base = true, $cdn = true, $isVersioned = null)
    {
        $dir = !$dir instanceof \Coast\Dir
            ? new \Coast\Dir("{$dir}")
            : $dir;
        return $this->path($dir, $base, $cdn, $isVersioned);
    }

    public function file($file, $base = true, $cdn = true, $isVersioned = null)
    {
        $file = !$file instanceof \Coast\File
            ? new \Coast\File("{$file}")
            : $file;
        return $this->path($file, $base, $cdn, $isVersioned);
    }

    public function path($path, $base = true, $cdn = true, $isVersioned = null)
    {
        $isVersioned = isset($isVersioned)
            ? $isVersioned
            : $this->_opts->isVersioned;

        $path = !$path instanceof \Coast\Path
            ? new \Coast\Path("{$path}")
            : $path;
        $class = get_class($path);
        $path = $path->isRelative()
            ? new $class($this->_opts->dir . "/{$path}")
            : $path;
        if (!$path->isWithin($this->_opts->dir)) {
            throw new \Coast\App\Exception("Path '{$path}' is not within base directory");
        }

        $url = $path->toRelative($this->_opts->dir);
        if ($base) {
            $url = $cdn
                ? $this->cdnBase() . $url
                : $this->dirBase() . $url;
        }
        $url = new \Coast\Url($url);

        if (isset($isVersioned) && $path instanceof \Coast\File && $path->exists()) {
            if ($isVersioned instanceof \Closure) {
                $isVersioned($url, $path);
            } else if ($isVersioned) {
                $url->queryParam('v', $path->modifyTime()->getTimestamp());
            }
        }

        return $url;
    }

    public function query(array $params = array(), $reset = false)
    {
        $url = new \Coast\Url();
        $url->queryParams($this->_parseQueryParams($params, $reset));
        
        return $url;
    }

    public function queryInputs(array $params = array(), $reset = false)
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

        return \Coast\array_filter_null_recursive($params);
    }
}