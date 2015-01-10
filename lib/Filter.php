<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Filter\Rule;

class Filter extends Rule
{
	protected $_steps = [];

	protected $_rules = [];

    public function step($step)
    {
    	$this->_steps[] = $step;
    	if ($step instanceof Rule) {
            $this->_rules[$step->name()][] = $step;
    	}
    	return $this;
    }

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

    public function rule($name)
    {
        return isset($this->_rules[$name])
            ? $this->_rules[$name]
            : null;
    }

    public function rules()
    {
        return $this->_rules;
    }

	public function __call($name, $args)
	{
        $class  = get_class() . '\\Rule\\' . ucfirst($name);
        $reflec = new \ReflectionClass($class);
        $step   = $reflec->newInstanceArgs($args);
		return $this->step($step);
	}

	public function _filter($value)
	{
		foreach ($this->_steps as $step) {
			if ($step instanceof Rule) {
				$value = $step($value);
			}
		}
		return $value;
	}

    public function __clone()
    {
        $steps = $this->_steps;
        $this->_steps = [];
        $this->_rules = [];
        foreach ($steps as $step) {
            if ($step instanceof Rule) {
                $step = clone $step;
            }
            $this->step($step);
        }
    }
}