<?php
/* 
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\File;
use Coast\App;
use Coast\App\Executable;
use Coast\Request;
use Coast\Response;
use Closure;
use ArrayAccess;

class Lazy implements Executable, ArrayAccess
{
    use Executable\Implementation;

	protected $_source;

	protected $_vars;

    protected $_value;

    public function __construct($source, $vars = array())
    {
        if (!$source instanceof File && !$source instanceof Closure) {
            throw new \Coast\Exception('Source must be an instance of Coast\File or Closure');
        }
    	$this->_source = $source;
    	$this->_vars   = $vars;
    }

    public function init()
    {
        if (isset($this->_value)) {
            return $this;
        } else if ($this->_source instanceof File) {
            $this->_value = \Coast\load($this->_source, $this->_vars);
        } else if ($this->_source instanceof Closure) {
            $this->_value = call_user_func($this->_source, $this->_vars);
        }
        return $this;
    }

    public function value()
    {
        $this->init();
        return $this->_value;
    }

    public function execute(Request $req, Response $res)
    {
        $this->init();
        if (!$this->_value instanceof Closure && !$this->_value instanceof Executable) {
            throw new App\Exception("Object is not a closure or instance of Coast\App\Executable");
        }
        return $this->_value->execute($req, $res);
    }

    public function __invoke()
    {
        $this->init();
        $args = func_get_args();
        return call_user_func_array($this->_value, $args);
    }

    public function __call($method, $args)
    {
        $this->init();
        return call_user_func_array([$this->_value, $method], $args);
    }

    public function __set($name, $value)
    {
        $this->init();
        $this->_value->{$name} = $value;
    }

    public function __isset($name)
    {
        $this->init();
        return isset($this->_value->{$name});
    }

    public function __get($name)
    {
        $this->init();
        return $this->_value->{$name};
    }

    public function __unset($name)
    {
        $this->init();
        unset($this->_value->{$name});
    }

    public function offsetSet($offset, $value)
    {
        $this->init();
        $this->_value[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        $this->init();
        return isset($this->_value[$offset]);
    }

    public function offsetGet($offset)
    {
        $this->init();
        return $this->_value[$offset];
    }

    public function offsetUnset($offset)
    {
        $this->init();
        unset($this->_value[$offset]);
    }
}