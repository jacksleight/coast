<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Validator\Rule;

class Validator extends Rule
{
	protected $_steps = [];

    public function steps(array $steps = null)
    {
        if (func_num_args() > 0) {
            foreach ($steps as $step) {
                $this->_steps[] = $step;
            }
            return $this;
        }
        return $this->_steps;
    }

    public function true($rule, $break = false)
    {
    	$this->_steps[] = [true, $this->_parse($rule), $break];
    	return $this;
    }

    public function false($rule, $break = false)
    {
    	$this->_steps[] = [false, $this->_parse($rule), $break];
    	return $this;
    }

	protected function _parse($rule)
	{
		if (!$rule instanceof Rule) {
			$name   = array_shift($rule);
			$class	= get_class() . '\\Rule\\' . ucfirst($name);
			$reflec	= new \ReflectionClass($class);
			$rule	= $reflec->newInstanceArgs($rule);
		}
		return $rule;
	}

	public function _validate($value)
	{
		foreach ($this->_steps as $step) {
			list($result, $rule, $break) = $step;
			if ($rule($value) != $result) {
				$this->_errors = array_merge($this->_errors, $rule->errors());
				if ($break) {
					break;
				}
			}
		}
	}
}