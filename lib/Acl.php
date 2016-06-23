<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Acl
{ 
    const NONE  = null;
    const ALLOW = true;
    const DENY  = false;

    protected $_delimiter = '/';

    protected $_roles = [];

    protected $_cache = null;

    public function delimiter($delimiter = null)
    {
        if (func_num_args() > 0) {
            $this->_delimiter = $delimiter;
            return $this;
        }
        return $this->_delimiter;
    }

    public function role($name, array $value = null)
    {
        if (func_num_args() > 1) {
            $value += [
                'extend' => null,
                'rules'  => [],
            ];
            if (isset($value['extend']) && !isset($this->_roles[$value['extend']])) {
                throw new Exception("Role '{$role}' does not exist");
            }
            $this->_roles[$name] = $value;
            $this->_cache = null;
            return $this;
        }
        return isset($this->_roles[$name])
            ? $this->_roles[$name]
            : null;
    }

    public function roles(array $roles = null)
    {
        if (func_num_args() > 0) {
            foreach ($roles as $resource => $type) {
                $this->role($resource, $type);
            }
            return $this;
        }
        return $this->_roles;
    }

    public function rule($role, $resource, $type = null)
    {
        if (!isset($this->_roles[$role])) {
            throw new Exception("Role '{$role}' does not exist");
        }

        $resource = trim($resource, $this->_delimiter) . $this->_delimiter;
        if (func_num_args() > 2) {
            if ($type instanceof Closure) {
                $type = $type->bindTo($this);
            }
            $this->_roles[$role]['rules'][$resource] = $type;
            $this->_cache = null;
            return $this;
        }
        return isset($this->_roles[$role]['rules'][$resource])
            ? $this->_roles[$role]['rules'][$resource]
            : null;
    }

    public function rules($role, array $rules = null)
    {
        if (func_num_args() > 1) {
            foreach ($rules as $name => $value) {
                $this->rule($role, $name, $value);
            }
            return $this;
        }
        return $this->_roles[$role]['rules'];
    }

    public function allow($role, $resource)
    {
        $this->rule($role, $resource, self::ALLOW);
        return $this;
    }

    public function deny($role, $resource)
    {
        $this->rule($role, $resource, self::DENY);
        return $this;
    }

    public function func($role, $resource, Closure $func)
    {
        $this->rule($role, $resource, $func);
        return $this;
    }

    public function isAllowed($role, $lookup, array $params = array())
    {
        if (!isset($this->_roles[$role])) {
            throw new Exception("Role '{$role}' does not exist");
        }

        if (!isset($this->_cache)) {
            $this->_cache = [];
            foreach ($this->_roles as $name => $value) {
                $rules = $value['rules'];
                while (isset($value['extend'])) {
                    $value = $this->_roles[$value['extend']];
                    $rules += $value['rules'];
                }
                ksort($rules);
                $this->_cache[$name] = $rules;
            }
        }

        $lookup = trim($lookup, $this->_delimiter) . $this->_delimiter;
        $result = self::NONE;
        foreach ($this->_cache[$role] as $resource => $type) {
            if ($resource === $this->_delimiter || strpos($lookup, $resource) === 0) {
                $result = $type instanceof Closure
                    ? $type($role, $params)
                    : $type;
                if ($result === self::DENY) {
                    break;
                }
            }
        }

        return $result;
    }

    public function __invoke($role, $lookup, array $params = array())
    {
        return $this->isAllowed($role, $lookup, $params);
    }
}