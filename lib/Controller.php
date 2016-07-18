<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Controller implements \Coast\App\Access, \Coast\Router\Routable
{
    use \Coast\App\Access\Implementation;
    
    protected $_nspaces = [];
    
    protected $_stack = [];
    
    protected $_history = [];
    
    protected $_actions = [];
    
    protected $_all;

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new \Coast\Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
    }

    public function nspace($name, $nspace = null)
    {
        if (func_num_args() > 0) {
            $this->_nspaces[$name] = $nspace;
            return $this;
        }
        return isset($this->_nspaces[$name])
            ? $this->_nspaces[$name]
            : null;
    }

    public function nspaces(array $nspaces = null)
    {
        if (func_num_args() > 0) {
            foreach ($nspaces as $name => $nspace) {
                $this->nspace($name, $nspace);
            }
            return $this;
        }
        return $this->_nspaces;
    }

    public function all($all = null)
    {
        if (func_num_args() > 0) {
            $this->_all = $all;
            return $this;
        }
        return $this->_all;
    }

    public function forward($action, $name = null, $group = null)
    {
        if (count($this->_history) > 0) {
            $item = $this->_history[count($this->_history) - 1];
            $name = isset($name)
                ? $name
                : $item[0];
            $params = $item[2];
            $group = isset($group)
                ? $group
                : $item[3];
        }
        $this->_stack = [];
        $this->_queue($name, $action, $params, $group);
    }

    public function stop()
    {
        $this->_stack = [];
    }

    public function dispatch($name, $action, $params = array(), $group = null)
    {
        if (!isset($group)) {
            reset($this->_nspaces);
            $group = key($this->_nspaces);
        }

        $this->_stack   = [];
        $this->_history = [];
        $this->_queue($name, $action, $params, $group);

        $result = null;
        while (count($this->_stack) > 0) {
            $item = array_shift($this->_stack);
            $this->_history[] = $item;
            list($name, $action, $params, $group) = $item;

            if (!isset($this->_nspaces[$group])) {
                throw new Controller\Exception("Controller group '{$group}' does not exist");
            }
            $class = $this->_nspaces[$group] . '\\' . implode('\\', array_map('ucfirst', explode('_', $name)));
            if (!isset($this->_actions[$class])) {
                if (!class_exists($class)) {
                    throw new Controller\Exception("Controller '{$group}:{$name}' does not exist");
                }
                $object = new $class($this);
                if (!$object instanceof \Coast\Controller\Action) {
                    throw new Controller\Exception("Controller '{$group}:{$name}' is not an instance of \Coast\Controller\Action");
                }
                $this->_actions[$class] = $object;
            } else {
                $object = $this->_actions[$class];
            }
            if (!method_exists($object, $action)) {
                throw new Controller\Exception("Controller action '{$group}:{$name}:{$action}' does not exist");
            }

            $result = call_user_func_array([$object, $action], $params);
            if (isset($result)) {
                $this->_stack = [];
            }
        }

        return $result;
    }

    protected function _queue($name, $action, $params, $group)
    {
        $parts = explode('_', $name);
        $path  = [];
        $names = [];
        while (count($parts) > 0) {
            $path[]  = array_shift($parts);
            $names[] = implode('_', $path);
        }

        $final = $names[count($names) - 1];

        $stack = [];
        foreach ($names as $name) {
            array_push($stack, [$name, 'preDispatch', $params, $group]);
        }
        array_push($stack, [$final, $action, $params, $group]);
        foreach (array_reverse($names) as $name) {
            array_push($stack, [$name, 'postDispatch', $params, $group]);
        }
        if (isset($this->_all)) {
            array_unshift($stack, [$this->_all[0], 'preDispatch', $params, $this->_all[1]]);
            array_push($stack, [$this->_all[0], 'postDispatch', $params, $this->_all[1]]);
        }

        foreach ($stack as $item) {
            if (!in_array($item, $this->_history)) {
                $this->_stack[] = $item;
            }
        }
    }

    public function route(\Coast\Request $req, \Coast\Response $res, array $route)
    {        
        $parts      = explode('_', $route['params']['controller']);
        $parts      = array_map('\Coast\str_camel_upper', $parts);
        $controller = implode('\\', $parts);
        $action     = \Coast\str_camel_lower($route['params']['action']);
        $group      = isset($route['params']['group']) ? $route['params']['group'] : null;
        return $this->dispatch(
            $controller,
            $action,
            [$req, $res],
            $group
        );
    }
}