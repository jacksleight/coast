<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
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

    const TYPE_STATIC = 'static';
    const TYPE_REGEX  = 'regex';

    use \Coast\App\Access\Implementation;
    use \Coast\App\Executable\Implementation;

    protected $_prefix;
    
    protected $_suffix;
    
    protected $_params = [];

    protected $_target;

    protected $_routes = [];

    protected $_aliases = [];

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

    public function route($name, $methods = null, $path = null, $params = null, callable $target = null)
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
            if (isset($target) && $target instanceof \Closure) {
                $target = $target->bindTo($this);
            }

            $path = ltrim($path, '/');
            if (isset($this->_prefix)) {
                $path = "{$this->_prefix}/{$path}";
            }
            if (isset($this->_suffix)) {
                $path = "{$path}/{$this->_suffix}";
            }
            if (strpos($path, '{') !== false) {
                $parts = explode('/', $path);
                $names = [];
                $stack = [];
                foreach ($parts as $i => $part) {
                    if (preg_match('/^\{([a-zA-Z0-9_-|]+)(?::(.*))?\}(\?)?$/', $part, $match)) {
                        $match = \Coast\array_merge_smart(
                            array('', '', '', ''),
                            $match
                        );
                        $keys  = explode('|', $match[1]);
                        $names = array_merge($names, $keys);
                        if (!strlen($match[2])) {
                            $regex = "([^\/]+)";
                        } elseif (count($keys) > 1) {
                            $regex = "(?:{$match[2]})";
                        } else {
                            $regex = "({$match[2]})";
                        }
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
                $type  = self::TYPE_REGEX;
                $value = '/^' . implode($stack) . '$/';
            } else {
                $names = [];
                $type  = self::TYPE_STATIC;
                $value = $path;
            }

            $route = [
                'methods' => $methods,
                'path'    => $path,
                'type'    => $type,
                'value'   => $value,
                'names'   => $names,
                'params'  => $params,
                'target'  => $target,
            ];
            $this->_routes[$name] = $route;
            return $this;
        }
        if (isset($this->_aliases[$name])) {
            $name = $this->_aliases[$name];
        }
        return isset($this->_routes[$name])
            ? $this->_routes[$name]
            : null;
    }

    public function alias($name, $value = null)
    {
        if (func_num_args() > 1) {
            if (isset($value)) {
                $this->_aliases[$name] = $value;
            } else {
                unset($this->_aliases[$name]);
            }
            return $this;
        }
        return isset($this->_aliases[$name])
            ? $this->_aliases[$name]
            : null;
    }

    public function aliases(array $aliases = null)
    {
        if (func_num_args() > 0) {
            foreach ($aliases as $name => $value) {
                $this->alias($name, $value);
            }
            return $this;
        }
        return $this->_aliases;
    }

    public function match($method, $path)
    {
        $method = strtoupper($method);
        end($this->_routes);
        do {
            $name  = key($this->_routes);
            $route = current($this->_routes);
            if (!in_array($method, $route['methods'])) {
                continue;
            }
            if ($route['type'] == self::TYPE_STATIC) {
                if ($path != $route['value']) {
                    continue;
                }
                $match = [];
            } else if ($route['type'] == self::TYPE_REGEX) {
                if (!preg_match($route['value'], $path, $match)) {
                    continue;
                }
                array_shift($match);
            }
            $params = array_merge(
                $this->_params,
                $route['params'],
                count($match) > 0
                    ? array_combine(array_slice($route['names'], 0, count($match)), $match)
                    : []
            );
            return array_merge($route, [
                'name'   => $name,
                'params' => $params,
            ]);
        } while (prev($this->_routes));

        throw new Router\Failure("Not route matched method '{$method}' and path '{$path}'");
    }

    public function reverse($name, array $params = array())
    {
        if (isset($this->_aliases[$name])) {
            $name = $this->_aliases[$name];
        }
        if (!isset($this->_routes[$name])) {
            throw new Router\Exception("Route '{$name}' does not exist");
        }

        $route    = $this->_routes[$name];
        $defaults = $route['params'] + $this->_params;
        $parts    = explode('/', $route['path']);
        $path     = [];
        $trim     = [];
        foreach ($parts as $i => $part) {
            if (preg_match('/^\{([a-zA-Z0-9_-]+)(?::(.*))?\}(\?)?$/', $part, $match)) {
                $match = \Coast\array_merge_smart(
                    array('', '', '', ''),
                    $match
                );
                if (isset($params[$match[1]])) {
                    $value  = $params[$match[1]];
                } else if (isset($defaults[$match[1]])) {
                    $value  = $defaults[$match[1]];
                    $trim[] = $i;
                } else {
                    $value  = null;
                    $trim[] = $i;
                }
            } else {
                $value = $part;
            }
            $path[$i] = $value;
        }

        for ($i = count($path) - 1; $i >= 0; $i--) {
            if (in_array($i, $trim)) {
                unset($path[$i]);
            } else {
                break;
            }
        }

        return implode('/', $path);
    }

    public function reverseData($name, array $params = array())
    {
        if (isset($this->_aliases[$name])) {
            $name = $this->_aliases[$name];
        }
        if (!isset($this->_routes[$name])) {
            throw new Router\Exception("Route '{$name}' does not exist");
        }

        $route    = $this->_routes[$name];
        $defaults = $route['params'] + $this->_params;

        return [
            'name'   => $name,
            'params' => $params + $defaults,
        ];
    }

    public function execute(\Coast\Request $req, \Coast\Response $res)
    {
        try {
            $route = $this->match($req->method(), $req->path());
        } catch (Router\Failure $e) {
            return;
        }

        $req->param('route', $route);
        $req->pathParams($route['params']);

        if (isset($route['target'])) {
            return $route['target']($req, $res, $route);
        } else if (isset($this->_target)) {
            return $this->_target->route($req, $res, $route);
        } else {
            throw new Router\Exception("There's nothing to route '{$route['name']}' to");
        }
    }
}