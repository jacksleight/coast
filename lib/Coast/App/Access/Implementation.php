<?php
/* 
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App\Access;

trait Implementation
{
    protected $_app;

    public function app(\Coast\App $app)
    {
        if (func_num_args() > 0) {
            $this->_app = $app;
            return $this;
        }
        return $this->_app;
    }

    public function __get($name)
    {
        return $this->_app->__get($name);
    }

    public function __isset($name)
    {
        return $this->_app->__isset($name);
    }

    public function __call($name, array $args)
    {
        return $this->_app->__call($name, $args);
    }
}