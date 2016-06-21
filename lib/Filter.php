<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Filter\Rule;
use Iterator;

class Filter extends Rule implements Iterator
{
    const STEP_BREAK = 'break';

	protected $_steps = [];

	protected $_rules = [];

    public function step($step, $index = null)
    {
        $index = !isset($index)
            ? count($this->_steps)
            : $index;
        array_splice($this->_steps, $index, 0, [$step]);
        if ($step instanceof Rule) {
            $this->_rules[$step->name()][] = $step;
        }
        return $this;
    }

    public function steps($steps = null, $index = null)
    {
        if (func_num_args() > 0) {
            foreach ($steps as $i => $step) {
                $this->step($step, isset($index) ? $index + $i : $index);
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
        if ($name == self::STEP_BREAK) {
            $step = $name;
        } else {
            $class  = get_class() . '\\Rule\\' . ucfirst($name);
            $reflec = new \ReflectionClass($class);
            $step   = $reflec->newInstanceArgs($args);
        }        
		return $this->step($step);
	}

	public function _filter($value)
	{
        if (!is_scalar($value)) {
            return $value;
        }
		foreach ($this->_steps as $step) {
			if ($step == self::STEP_BREAK && !strlen($value)) {
                break;
            } else if ($step instanceof Rule) {
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

    public function rewind() 
    {
        reset($this->_steps);
    }

    public function current() 
    {
        return current($this->_steps);
    }

    public function key() 
    {
        return key($this->_steps);
    }

    public function next() 
    {
        next($this->_steps);
    }

    public function valid() 
    {
        return key($this->_steps) !== null;
    } 
}