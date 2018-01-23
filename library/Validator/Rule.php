<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator;
use JsonSerializable;

abstract class Rule implements JsonSerializable
{
	protected $_name;

	protected $_errors = [];

	public function __construct()
	{}

	public function name($name = null)
    {
        if (func_num_args() > 0) {
            $this->_name = $name;
            return $this;
        }
        if (isset($this->_name)) {
        	return $this->_name;
        }
        $parts = explode('\\', get_class($this));
		return lcfirst(array_pop($parts));
    }

	public function params()
	{
		$params = [];
		foreach (get_object_vars($this) as $key => $value) {
			if (in_array($key, ['_name', '_errors'])) {
				continue;
			}
			$params[ltrim($key, '_')] = $value;
		}
		return $params;
	}

	abstract protected function _validate($value);

	public function validate($value, $context = null)
	{
		$this->_errors = [];
		$this->_validate($value, $context);
		return !count($this->_errors);
	}

	public function isValid()
	{
		return !count($this->_errors);
	}

	public function __invoke($value, $context = null)
	{
		return $this->validate($value, $context);
	}

	public function error($error = null)
	{
		if (!is_array($error)) {
			$error = [$this->name(), $error, $this->params()];
		}
		$this->_errors[] = $error;
		return $this;
	}

	public function errors(array $errors = null)
	{
		if (func_num_args() > 0) {
			foreach ($errors as $error) {
				$this->error($error);
				return $this;
			}
		}
		return $this->_errors;
	}

    public function jsonSerialize()
    {
        return [
			'name'		=> $this->name(),
			'params'	=> $this->params(),
        ];
    }
}