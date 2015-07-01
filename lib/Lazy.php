<?php
/* 
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\File;
use Closure;

class Lazy
{
	protected $_source;
	protected $_vars;

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
        if ($this->_source instanceof File) {
            return \Coast\load($this->_source, $this->_vars);
        } else if ($this->_source instanceof Closure) {
            return call_user_func($this->_source, $this->_vars);
        }
    }
}