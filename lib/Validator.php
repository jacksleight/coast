<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Validator\Rule;

class Validator extends Rule
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

    public function steps(array $steps = null, $index = null)
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
            $not = false;
            if (preg_match('/^not(\w+)$/', $name, $match)) {
                $not  = true;
                $name = $match[1];
            }
            $map = [
                'array' => 'arr',
            ];
            if (isset($map[$name])) {
                $name = $map[$name];
            }
            $class  = get_class() . '\\Rule\\' . ucfirst($name);
            $reflec = new \ReflectionClass($class);
            $step   = $reflec->newInstanceArgs($args);
            if ($not) {
                $step = new Rule\Not($step);
            }
        }
        return $this->step($step);
    }

    public function _validate($value)
    {
        $result = null;
        foreach ($this->_steps as $step) {
            if ($step == self::STEP_BREAK && $result === false) {
                break;
            } else if (is_callable($step)) {
                $result = $step($value);
                if (!$result) {
                    $this->_errors = array_merge($this->_errors, $step->errors());
                }
            }    
        }
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