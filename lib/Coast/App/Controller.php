<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class Controller implements \Coast\App\Access, \Coast\App\Routable
{
    use \Coast\App\Access\Implementation;
    
    protected $_classNamespaces = [];
    
    protected $_stack = [];
    
    protected $_history = [];
    
    protected $_actions = [];

    public function __construct($classNamespaces)
    {
        if (!is_array($classNamespaces)) {
            $classNamespaces = [$classNamespaces];
        }
        $this->classNamespaces($classNamespaces);
    }

    public function classNamespace($name, $classNamespace = null)
    {
        if (isset($classNamespace)) {
            $this->_classNamespaces[$name] = $classNamespace;
            return $this;
        }
        return isset($this->_classNamespaces[$name])
            ? $this->_classNamespaces[$name]
            : null;
    }

    public function classNamespaces(array $classNamespaces = null)
    {
        if (isset($classNamespaces)) {
            foreach ($classNamespaces as $name => $classNamespace) {
                $this->classNamespace($name, $classNamespace);
            }
            return $this;
        }
        return $this->_classNamespaces;
    }

    public function forward($action, $name = null, $set = null)
    {
        if (count($this->_history) > 0) {
            $item = $this->_history[count($this->_history) - 1];
            $name = isset($name)
                ? $name
                : $item[0];
            $params = $item[2];
            $set = isset($set)
                ? $set
                : $item[3];
        }
        $this->_stack = array();
        $this->_queue($name, $action, $params, $set);
    }

    public function dispatch($name, $action, $params = array(), $set = null)
    {
        if (!isset($set)) {
            reset($this->_classNamespaces);
            $set = key($this->_classNamespaces);
        }

        $this->_stack   = [];
        $this->_history = [];
        $this->_queue($name, $action, $params, $set);

        $result = null;
        while (count($this->_stack) > 0) {
            $item = array_shift($this->_stack);
            $this->_history[] = $item;
            list($name, $action, $params) = $item;

            if (!isset($this->_classNamespaces[$set])) {
                throw new \Coast\App\Exception("Controller set '{$set}' does not exist");
            }
            $class = $this->_classNamespaces[$set] . '\\' . implode('\\', array_map('ucfirst', explode('_', $name)));
            if (!isset($this->_actions[$class])) {
                if (!class_exists($class)) {
                    throw new \Coast\App\Exception("Controller '{$set}:{$name}' does not exist");
                }
                $object = new $class($this);
                if (!$object instanceof \Coast\App\Controller\Action) {
                    throw new \Coast\App\Exception("Controller '{$set}:{$name}' is not an instance of \Coast\App\Controller\Action");
                }
                $this->_actions[$class] = $object;
            } else {
                $object = $this->_actions[$class];
            }
            if (!method_exists($object, $action)) {
                throw new \Coast\App\Exception("Controller action '{$set}:{$name}:{$action}' does not exist");
            }

            $result = call_user_func_array([$object, $action], $params);
            if (isset($result)) {
                $this->_stack = [];
            }
        }

        return $result;
    }

    protected function _queue($name, $action, $params, $set)
    {
        $parts = explode('_', $name);
        $path  = [];
        $names = ['all'];
        while (count($parts) > 0) {
            $path[]  = array_shift($parts);
            $names[] = implode('_', $path);
        }

        $final = $names[count($names) - 1];

        $stack = [];
        foreach ($names as $name) {
            $stack[] = [$name, 'preDispatch', $params, $set];
        }
        $stack[] = [$final, $action, $params, $set];
        foreach (array_reverse($names) as $name) {
            $stack[] = [$name, 'postDispatch', $params, $set];
        }

        foreach ($stack as $item) {
            if (!in_array($item, $this->_history)) {
                $this->_stack[] = $item;
            }
        }
    }

    public function route(\Coast\App\Request $req, \Coast\App\Response $res)
    {        
        $parts      = explode('_', $req->controller);
        $parts      = array_map('\Coast\str_camel_upper', $parts);
        $controller = implode('\\', $parts);
        $action     = \Coast\str_camel_lower($req->action);
        $set        = isset($req->set) ? \Coast\str_camel_lower($req->set) : null;
        return $this->dispatch(
            $controller,
            $action,
            [$req, $res],
            $set
        );
    }
}