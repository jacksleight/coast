<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;

class ObjectType extends Rule
{
	protected $_name = 'object';

    protected $_className;

    public function __construct($className = null)
    {
        $this->className($className);
    }

    public function className($className = null)
    {
        if (func_num_args() > 0) {
            $this->_className = $className;
            return $this;
        }
        return $this->_className;
    }

	protected function _validate($value)
	{
		if (!is_object($value)) {
		 	$this->error();
		} else if (isset($this->_className) && !$value instanceof $this->_className) {
            $this->error();
        }
	}
}