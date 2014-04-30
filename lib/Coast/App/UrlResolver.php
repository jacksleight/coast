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

    protected $_isVersioned = true;

    protected $_versionCallback;

    public function __construct(\Coast\Url $baseUrl, \Coast\Dir $baseDir = null, \Coast\App\Router $router = null)
    {
        $this->baseUrl($baseUrl);
        $this->baseDir($baseDir);
        $this->router($router);
    }

    public function call()
    {
        $args = func_get_args();
        if (!isset($args[0])) {
            $method = 'baseUrl';
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

    public function baseUrl(\Coast\Url $baseUrl = null)
    {
        if (isset($baseUrl)) {
            $this->_baseUrl = $baseUrl;
            return $this;
        }
        return $this->_baseUrl;
    }

    public function cdnUrl(\Coast\Url $cdnUrl = null)
    {
        if (isset($cdnUrl)) {
            $this->_cdnUrl = $cdnUrl;
            return $this;
        }
        return $this->_cdnUrl;
    }

    public function baseDir(\Coast\Dir $baseDir = null)
    {
        if (isset($baseDir)) {
            $this->_baseDir = $baseDir;
            return $this;
        }
        return $this->_baseDir;
    }

    public function router(\Coast\App\Router $router = null)
    {
        if (isset($router)) {
            $this->_router = $router;
            return $this;
        }
        return $this->_router;
    }

    public function isVersioned($isVersioned = null)
    {
        if (isset($isVersioned)) {
            $this->_isVersioned = $isVersioned;
            return $this;
        }
        return $this->_isVersioned;
    }

    public function versionCallback(\Closure $versionCallback = null)
    {
        if (isset($versionCallback)) {
            $this->_versionCallback = $versionCallback;
            return $this;
        }
        return $this->_versionCallback;
    }

    public function string($string, $isBased = true)
    {
        $path = (string) $string;
        return new \Coast\Url($isBased
            ? $this->_baseUrl . $path
            : $path);
    }

    public function route(array $params = array(), $name = null, $reset = false, $isBased = true)
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
        return new \Coast\Url($isBased
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

    public function dir($dir, $isBased = true, $isCdnd = true, $isVersioned = null)
    {
        $dir = !$dir instanceof \Coast\Dir
            ? new \Coast\Dir("{$dir}")
            : $dir;
        return $this->path($dir, $isBased, $isCdnd, $isVersioned);
    }

    public function file($file, $isBased = true, $isCdnd = true, $isVersioned = null)
    {
        $file = !$file instanceof \Coast\File
            ? new \Coast\File("{$file}")
            : $file;
        return $this->path($file, $isBased, $isCdnd, $isVersioned);
    }

    public function path($path, $isBased = true, $isCdnd = true, $isVersioned = null)
    {
        if (!isset($this->_baseDir)) {
            throw new \Coast\App\Exception("Base directory has not been set");
        }

        $isVersioned = isset($isVersioned)
            ? $isVersioned
            : $this->_isVersioned;

        $path = !$path instanceof \Coast\Path
            ? new \Coast\Path("{$path}")
            : $path;
        $class = get_class($path);
        $path = $path->isRelative()
            ? new $class("{$this->_baseDir}/{$path}")
            : $path;
        if (!$path->isWithin($this->_baseDir)) {
            throw new \Coast\App\Exception("Path '{$path}' is not within base directory '{$this->_baseDir}'");
        }

        $url = new \Coast\Url($path->toRelative($this->_baseDir));
        if ($isBased) {
            $url = new \Coast\Url($isCdnd && isset($this->_cdnUrl)
                ? $this->_cdnUrl . $url
                : $this->_baseUrl . $url);
        }

        if ($isVersioned && $path instanceof \Coast\File && $path->exists()) {
            if (isset($this->_versionCallback)) {
                $this->_versionCallback($url, $path);
            } else {
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