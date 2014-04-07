<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class Controller implements \Coast\App\Access, \Coast\App\Routable
{
    use \Coast\App\Access\Implementation;
    use \Coast\Options;
    
    protected $_stack   = [];
    protected $_history = [];
    protected $_actions = [];

    public function __construct(array $options = array())
    {
        $this->options(array_merge([
            'namespace' => null,
        ], $options));
    }

    public function forward($action, $name = null)
    {
        if (count($this->_history) > 0) {
            $item = $this->_history[count($this->_history) - 1];
            $name = isset($name)
                ? $name
                : $item[0];
            $params = $item[2];
        }
        $this->_stack = array();
        $this->_queue($name, $action, $params);
    }

    public function dispatch($name, $action, $params = array())
    {
        $this->_stack   = [];
        $this->_history = [];
        $this->_queue($name, $action, $params);

        $result = null;
        while (count($this->_stack) > 0) {
            $item = array_shift($this->_stack);
            $this->_history[] = $item;
            list($name, $action, $params) = $item;

            $class = $this->_options->namespace . '\\' . implode('\\', array_map('ucfirst', explode('_', $name)));
            if (!isset($this->_actions[$class])) {
                if (!class_exists($class)) {
                    throw new \Coast\App\Exception("Controller '{$name}' does not exist");
                }
                $object = new $class($this);
                if (!$object instanceof \Coast\App\Controller\Action) {
                    throw new \Coast\App\Exception("Controller '{$name}' is not an instance of \Coast\App\Controller\Action");
                }
                $this->_actions[$class] = $object;
            } else {
                $object = $this->_actions[$class];
            }
            if (!method_exists($object, $action)) {
                throw new \Coast\App\Exception("Controller action '{$name}::{$action}' does not exist");
            }

            $result = call_user_func_array([$object, $action], $params);
            if (isset($result)) {
                $this->_stack = [];
            }
        }

        return $result;
    }

    protected function _queue($name, $action, $params)
    {
        $parts = explode('_', $name);
        $path  = [];
        $names = ['all'];
        while (count($parts) > 0) {
            $path[]     = array_shift($parts);
            $names[] = implode('_', $path);
        }

        $final = $names[count($names) - 1];

        $stack = [];
        foreach ($names as $name) {
            $stack[] = [$name, 'preDispatch', $params];
        }
        $stack[] = [$final, $action, $params];
        foreach (array_reverse($names) as $name) {
            $stack[] = [$name, 'postDispatch', $params];
        }

        foreach ($stack as $item) {
            if (!in_array($item, $this->_history)) {
                $this->_stack[] = $item;
            }
        }
    }

    public function route(\Coast\App\Request $req, \Coast\App\Response $res)
    {        
        return $this->dispatch(
            \Coast\str_camel_upper($req->param('controller')),
            \Coast\str_camel_upper($req->param('action')),
            [$req, $res]
        );
    }
}