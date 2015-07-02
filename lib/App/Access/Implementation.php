<?php
/* 
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App\Access;

trait Implementation
{
    protected $_app;

    public function app(\Coast\App $app = null)
    {
        if (func_num_args() > 0 && !isset($this->_app)) {
            $this->_app = $app;
            return $this;
        }
        return $this->_app;
    }

    public function __get($name)
    {
        if (!isset($this->_app)) {
            throw new \Exception("Property '" . __CLASS__ . "::\${$name}' does not exist and \$app has not been set");
        }
        return $this->_app->__get($name);
    }

    public function __isset($name)
    {
        if (!isset($this->_app)) {
            throw new \Exception("Property '" . __CLASS__ . "::\${$name}' does not exist and \$app has not been set");
        }
        return $this->_app->__isset($name);
    }

    public function __call($name, array $args)
    {
        if (!isset($this->_app)) {
            throw new \Exception("Method '" . __CLASS__ . "::{$name}()' does not exist and \$app has not been set");
        }
        return $this->_app->__call($name, $args);
    }
}