<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App\Controller;

use Coast\App\Request,
    Coast\App\Response;

abstract class Action
{
    protected $_controller;

    public function __construct(\Coast\App\Controller $controller)
    {
        $this->_controller = $controller;
    }

    public function __get($name)
    {
        return $this->_controller->$name;
    }

    public function __isset($name)
    {
        return isset($this->_controller->$name);
    }

    public function __call($name, array $args)
    {
        return call_user_func_array(array($this->_controller, $name), $args);
    }

    public function preDispatch(Request $req, Response $res)
    {}

    public function postDispatch(Request $req, Response $res)
    {}
}