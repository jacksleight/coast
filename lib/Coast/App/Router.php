<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class Router implements \Coast\App\Access, \Coast\App\Executable
{
    const METHOD_GET    = \Coast\App\Request::METHOD_GET;
    const METHOD_POST   = \Coast\App\Request::METHOD_POST;
    const METHOD_PUT    = \Coast\App\Request::METHOD_PUT;
    const METHOD_DELETE = \Coast\App\Request::METHOD_DELETE;

    use \Coast\App\Access\Implementation;
    use \Coast\Options;

    protected $_routes = [];

    public function __construct(array $options = array())
    {
        $this->options(array_merge([
            'target' => null,
        ], $options));
    }

    public function app(\Coast\App $app)
    {
        $this->_app = $app;
        if ($this->_options->target instanceof \Coast\App\Access) {
            $this->_options->target->app($app);
        }
        return $this;
    }

    public function all($name, $path, $params = null, \Closure $target = null)
    {
        return $this->add($name, [
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_DELETE,
        ], $path, $params, $target);
    }

    public function get($name, $path, $params = null, \Closure $target = null)
    {
        return $this->add($name, [
            self::METHOD_GET,
        ], $path, $params, $target);
    }

    public function post($name, $path, $params = null, \Closure $target = null)
    {
        return $this->add($name, [
            self::METHOD_POST,
        ], $path, $params, $target);
    }

    public function put($name, $path, $params = null, \Closure $target = null)
    {
        return $this->add($name, [
            self::METHOD_PUT,
        ], $path, $params, $target);
    }

    public function delete($name, $path, $params = null, \Closure $target = null)
    {
        return $this->add($name, [
            self::METHOD_DELETE,
        ], $path, $params, $target);
    }

    public function add($name, $methods, $path, $params = null, \Closure $target = null)
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }
        foreach ($methods as $i => $method) {
            $methods[$i] = strtoupper($method);
        }
        if ($params instanceof \Closure) {
            $target = $params;
            $params = [];
        } if (!isset($params)) {
            $params = [];
        }
        if (isset($target)) {
            $target = $target->bindTo($this);
        }

        $parts = explode('/', ltrim($path, '/'));
        $names = [];
        $stack = [];
        foreach ($parts as $i => $part) {
            if (preg_match('/^\{([a-zA-Z0-9_-]+)(?::(.*))?\}(\?)?$/', $part, $match)) {
                $match = \Coast\array_merge_smart(
                    array('', '', '', ''),
                    $match
                );
                $names[] = $match[1];    
                $regex = strlen($match[2])
                    ? "({$match[2]})"
                    : "([a-zA-Z0-9_-]+)";
                if ($match[3] == '?') {
                    $regex = $i == 0 
                        ? "(?:{$regex})?"
                        : "(?:\/{$regex})?";
                } else {
                    $regex = $i == 0
                        ? "{$regex}"
                        : "\/{$regex}";
                }
            } else {
                $regex = $i == 0
                    ? preg_quote($part, '/')
                    : "\/" . preg_quote($part, '/');
            }
            $stack[] = $regex;
        }
        $regex = '/^' . implode($stack) . '$/';

        $route = [
            'methods' => $methods,
            'path'    => $path,
            'regex'   => $regex,
            'names'   => $names,
            'params'  => $params,
            'target'  => $target,
        ];
        $this->_routes = array_merge([$name => $route], $this->_routes);
        return $this;
    }

    public function match($method, $path)
    {
        foreach ($this->_routes as $name => $route) {
            if (!in_array($method, $route['methods'])) {
                continue;
            }
            if (!preg_match($route['regex'], $path, $match)) {
                continue;
            }
            array_shift($match);    
            $params = array_merge(
                $route['params'],
                count($match) > 0
                    ? array_combine(array_slice($route['names'], 0, count($match)), $match)
                    : []
            );
            return array_merge($route, [
                'name'   => $name,
                'params' => $params,
            ]);
        }        
        return false;
    }

    public function reverse($name, array $params = array())
    {
        if (!isset($this->_routes[$name])) {
            throw new \Coast\App\Exception("Route '{$name}' does not exist");
        }

        $route = $this->_routes[$name];
        $parts = explode('/', $route['path']);
        $path  = [];
        foreach ($parts as $i => $part) {
            if (preg_match('/^\{([a-zA-Z0-9_-]+)(?::(.*))?\}(\?)?$/', $part, $match)) {
                $match = \Coast\array_merge_smart(
                    array('', '', '', ''),
                    $match
                );
                if (isset($params[$match[1]])) {
                    $value = $params[$match[1]];
                } else if ($match[3] == '?') {
                    $value = null;
                } else {
                    throw new \Coast\App\Exception("Parameter '{$match[1]}' missing");
                }
            } else {
                $value = $part;
            }
            $path[$i] = $value;
        }
        while (count($path) > 0 && !isset($path[count($path) - 1])) {
            array_pop($path);
        }
        return implode('/', $path);
    }

    public function execute(\Coast\App\Request $req, \Coast\App\Response $res)
    {
        $match = $this->match($req->method(), $req->path());
        if (!$match) {
            return false;
        }
        $req->params(array_merge([
            'route' => $match,
        ], $match['params']));
        
        if (isset($this->_options->target)) {
            return $this->_options->target->route($req, $res);
        } else if (isset($match['target'])) {
            return $match['target']($req, $res, $this->app);
        } else {
            throw new \Coast\App\Exception("There's noting to route '{$match['name']}' to");
        }        
    }
}