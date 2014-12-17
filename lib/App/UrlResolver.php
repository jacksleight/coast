<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class UrlResolver implements \Coast\App\Access
{
    use \Coast\App\Access\Implementation;

    protected $_baseUrl;

    protected $_cdnUrl;

    protected $_baseDir;

    protected $_router;

    protected $_cacheBuster;

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
    }

    public function __invoke()
    {
        $args = func_get_args();
        if (!isset($args[0])) {
            $func = array($this, 'string');
        } else if (is_array($args[0])) {
            $func = array($this, 'route');
        } else if ($args[0] instanceof \Coast\Url) {
            $func = array($this, 'url');
        } else if ($args[0] instanceof \Coast\Dir) {
            $func = array($this, 'dir');
        } else if ($args[0] instanceof \Coast\File) {
            $func = array($this, 'file');
        } else if ($args[0] instanceof \Coast\Path) {
            $func = array($this, 'path');
        } else {
            $func = array($this, 'string');
        }
        return call_user_func_array($func, $args);
    }

    public function baseUrl(\Coast\Url $baseUrl = null)
    {
        if (func_num_args() > 0) {
            $this->_baseUrl = $baseUrl;
            return $this;
        }
        return $this->_baseUrl;
    }

    public function cdnUrl(\Coast\Url $cdnUrl = null)
    {
        if (func_num_args() > 0) {
            $this->_cdnUrl = $cdnUrl;
            return $this;
        }
        return $this->_cdnUrl;
    }

    public function baseDir(\Coast\Dir $baseDir = null)
    {
        if (func_num_args() > 0) {
            $this->_baseDir = $baseDir;
            return $this;
        }
        return $this->_baseDir;
    }

    public function router(\Coast\App\Router $router = null)
    {
        if (func_num_args() > 0) {
            $this->_router = $router;
            return $this;
        }
        return $this->_router;
    }

    public function cacheBuster(\Closure $cacheBuster = null)
    {
        if (func_num_args() > 0) {
            $this->_cacheBuster = $cacheBuster;
            return $this;
        }
        return $this->_cacheBuster;
    }

    public function string($string = null, $base = true)
    {
        $path = (string) $string;
        return new \Coast\Url($base
            ? $this->_baseUrl . $path
            : $path);
    }

    public function route(array $params = array(), $name = null, $reset = false, $base = true)
    {
        if (!isset($this->_router)) {
            throw new \Coast\App\Exception("Router has not been set");
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
        $path = ltrim($this->_router->reverse($name, $params), '/');
        return new \Coast\Url($base
            ? $this->_baseUrl . $path
            : $path);
    }

    public function url($url)
    {
        $url = !$url instanceof \Coast\Url
            ? new \Coast\Url("{$url}")
            : $url;
        return $url;
    }

    public function dir($dir, $base = true, $cdn = true, $cacheBust = true)
    {
        $dir = !$dir instanceof \Coast\Dir
            ? new \Coast\Dir("{$dir}")
            : $dir;
        return $this->path($dir, $base, $cdn, $cacheBust);
    }

    public function file($file, $base = true, $cdn = true, $cacheBust = true)
    {
        $file = !$file instanceof \Coast\File
            ? new \Coast\File("{$file}")
            : $file;
        return $this->path($file, $base, $cdn, $cacheBust);
    }

    public function path($path, $base = true, $cdn = true, $cacheBust = true)
    {
        if (!isset($this->_baseDir)) {
            throw new \Coast\App\Exception("Base directory has not been set");
        }

        $path = !$path instanceof \Coast\Path
            ? new \Coast\Path("{$path}")
            : $path;
        $class = get_class($path);
        $path = $path->isRelative()
            ? new $class("{$this->_baseDir}/{$path}")
            : $path;
        $path = $path->toReal();

        $url = $path->toRelative($this->_baseDir);
        if ($base) {
            if ($cdn && isset($this->_cdnUrl)) {
                $url = $this->_cdnUrl . $url;
            } else {
                $url = $this->_baseUrl . $url;
            }
        }
        $url = (new \Coast\Url())->path($url);

        if ($cacheBust && isset($this->_cacheBuster) && $path instanceof \Coast\File && $path->isReadable()) {
            $callback = $this->_cacheBuster;
            $callback($url, $path);
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