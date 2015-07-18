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

    protected $_content;

    public function __construct($source, $vars = array())
    {
        if (!$source instanceof File && !$source instanceof Closure) {
            throw new \Coast\Exception('Source must be an instance of Coast\File or Closure');
        }
    	$this->_source = $source;
    	$this->_vars   = $vars;
    }

    public function load()
    {
        if (isset($this->_content)) {
            return $this;
        } else if ($this->_source instanceof File) {
            $this->_content = \Coast\load($this->_source, $this->_vars);
        } else if ($this->_source instanceof Closure) {
            $this->_content = call_user_func($this->_source, $this->_vars);
        }
        return $this;
    }

    public function content()
    {
        $this->load();
        return $this->_content;
    }

    public function execute(Request $req, Response $res)
    {
        $this->load();
        if (!$this->_content instanceof \Closure && !$this->_content instanceof Executable) {
            throw new App\Exception("Object is not a closure or instance of Coast\App\Executable");
        }
        return $this->_content->execute($req, $res);
    }

    public function __invoke()
    {
        $this->load();
        $args = func_get_args();
        return call_user_func_array($this->_content, $args);
    }

    public function __call($method, $args)
    {
        $this->load();
        return call_user_func_array([$this->_content, $method], $args);
    }

    public function __set($name, $value)
    {
        $this->load();
        $this->_content->{$name} = $value;
    }

    public function __isset($name)
    {
        $this->load();
        return isset($this->_content->{$name});
    }

    public function __get($name)
    {
        $this->load();
        return $this->_content->{$name};
    }

    public function __unset($name)
    {
        $this->load();
        unset($this->_content->{$name});
    }

    public function offsetSet($offset, $value)
    {
        $this->load();
        $this->_content[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        $this->load();
        return isset($this->_content[$offset]);
    }

    public function offsetGet($offset)
    {
        $this->load();
        return $this->_content[$offset];
    }

    public function offsetUnset($offset)
    {
        $this->load();
        unset($this->_content[$offset]);
    }
}