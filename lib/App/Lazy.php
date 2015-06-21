<?php
/* 
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class Lazy
{
	protected $file;
	protected $vars;

    public function __construct(\Coast\File $file, $vars = array())
    {
    	$this->file = $file;
    	$this->vars = $vars;
    }

    public function load()
    {
    	return \Coast\load($this->file, $this->vars);
    }
}