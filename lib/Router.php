<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Router implements \Coast\App\Access, \Coast\App\Executable
{
    const METHOD_ALL    = 'ALL';
    const METHOD_GET    = \Coast\Request::METHOD_GET;
    const METHOD_POST   = \Coast\Request::METHOD_POST;
    const METHOD_PUT    = \Coast\Request::METHOD_PUT;
    const METHOD_DELETE = \Coast\Request::METHOD_DELETE;

    use \Coast\App\Access\Implementation;

    protected $_prefix;
    
    protected $_suffix;
    
    protected $_params = [];

    protected $_target;

    protected $_routes = [];

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new \Coast\Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
    }

    public function prefix($prefix = null)
    {
        if (func_num_args() > 0) {
            $this->_prefix = $prefix;
            return $this;
        }
        return $this->_prefix;
    }

    public function suffix($suffix = null)
    {
        if (func_num_args() > 0) {
            $this->_suffix = $suffix;
            return $this;
        }
        return $this->_suffix;
    }

    public function params(array $params = null)
    {
        if (func_num_args() > 0) {
            $this->_params = $params;
            return $this;
        }
        return $this->_params;
    }

    public function target(\Coast\Router\Routable $target = null)
    {
        if (func_num_args() > 0) {
            $this->_target = $target;
            return $this;
        }
        return $this->_target;
    }

    public function routes(array $routes = array())
    {
        if (func_num_args() > 0) {
            foreach ($routes as $name => $route) {
                call_user_func_array([$this, 'route'], array_merge([$name], $route));
            }
            return $this;
        }
        return $this->_routes;
    }

    public function route($name, $methods = null, $path = null, $params = null, \Closure $target = null)
    {
        if (func_num_args() > 1) {
            if (!is_array($methods)) {
                $methods = array_map('trim', explode(',', $methods));
            }
            foreach ($methods as $i => $method) {
                $method = strtoupper($method);
                if ($method == self::METHOD_ALL) {
                    $methods = [
                        self::METHOD_GET,
                        self::METHOD_POST,
                        self::METHOD_PUT,
                        self::METHOD_DELETE,
                    ];
                    break;
                }
                $methods[$i] = $method;
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

            if (isset($this->_prefix)) {
                $path = "{$this->_prefix}/{$path}";
            }
            if (isset($this->_suffix)) {
                $path = "{$path}/{$this->_suffix}";
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
            $this->_routes = [$name => $route] + $this->_routes;
            return $this;
        }
        return isset($this->_routes[$name])
            ? $this->_routes[$name]
            : null;
    }

    public function match($method, $path)
    {
        $method = strtoupper($method);
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
            throw new Router\Exception("Route '{$name}' does not exist");
        }

        $route  = $this->_routes[$name];
        $params = $params + $this->_params;
        $parts  = explode('/', $route['path']);
        $path   = [];
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
                    throw new Router\Exception("Parameter '{$match[1]}' missing");
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

    public function execute(\Coast\Request $req, \Coast\Response $res)
    {
        $route = $this->match($req->method(), $req->path());
        if (!$route) {
            return false;
        }
        $req->params(array_merge([
            'route' => $route,
        ], $route['params']));
        
        if (isset($route['target'])) {
            return $route['target']($req, $res, $this->app);
        } else if (isset($this->_target)) {
            return $this->_target->route($req, $res);
        } else {
            throw new Router\Exception("There's nothing to route '{$route['name']}' to");
        }        
    }
}