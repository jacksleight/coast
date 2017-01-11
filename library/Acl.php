<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Closure;

class Acl
{ 
    const NONE  = null;
    const ALLOW = true;
    const DENY  = false;

    protected $_roles = [];

    public function role($name, array $value = null)
    {
        if (func_num_args() > 1) {
            $value += [
                'extend' => null,
                'rules'  => [],
            ];
            if (isset($value['extend']) && !isset($this->_roles[$value['extend']])) {
                throw new Exception("Role '{$value['extend']}' does not exist");
            }
            $this->_roles[$name] = $value;
            return $this;
        }
        return isset($this->_roles[$name])
            ? $this->_roles[$name]
            : null;
    }

    public function roles(array $roles = null)
    {
        if (func_num_args() > 0) {
            foreach ($roles as $name => $value) {
                $this->role($name, $value);
            }
            return $this;
        }
        return $this->_roles;
    }

    public function rule($role, $resource, $operations, $action)
    {
        if (!isset($this->_roles[$role])) {
            throw new Exception("Role '{$role}' does not exist");
        }

        if (!is_array($operations)) {
            $operations = array_map('trim', explode(',', $operations));
        }
        if ($action instanceof Closure) {
            $action = $action->bindTo($this);
        }
        $this->_roles[$role]['rules'][] = [
            $resource,
            $operations,
            $action,
        ];
        return $this;
    }

    public function rules($role, array $rules = null)
    {
        if (func_num_args() > 1) {
            foreach ($rules as $rule) {
                call_user_func_array([$this, 'rule'], array_merge([$role], $rule));
            }
            return $this;
        }
        return $this->_roles[$role]['rules'];
    }

    public function allow($role, $resource, $operations)
    {
        return $this->rule($role, $resource, $operations, $action, self::ALLOW);
    }

    public function deny($role, $resource, $operations)
    {
        return $this->rule($role, $resource, $operations, $action, self::DENY);
    }

    public function func($role, $resource, $operations, Closure $func)
    {
        return $this->rule($role, $resource, $operations, $action, $func);
    }

    public function check($role, $resource, $operation, array $params = array())
    {
        if (!isset($this->_roles[$role])) {
            throw new Exception("Role '{$role}' does not exist");
        }

        $role  = $this->_roles[$role];

        $value = $role;
        $rules = $value['rules'];
        while (isset($value['extend'])) {
            $value = $this->_roles[$value['extend']];
            $rules = array_merge($value['rules'], $rules);
        }

        $action = self::NONE;
        end($rules);
        do {
            $rule = current($rules);
            if ($resource != $rule[0]) {
                continue;
            }
            if ($rule[1] !== ['*'] && !in_array($operation, $rule[1])) {
                continue;
            }
            $action = $rule[2];
            $action = $action instanceof Closure
                ? call_user_func_array($action, array_merge([$role], $params))
                : $action;
            if ($action !== self::NONE) {
                break;
            }
        } while (prev($rules));

        return $action;
    }

    public function isAllow($role, $resource, $operation, array $params = array())
    {
        return $this->check($role, $resource, $operation, $params) === self::ALLOW;
    }

    public function isDeny($role, $resource, $operation, array $params = array())
    {
        return $this->check($role, $resource, $operation, $params) === self::DENY;
    }

    public function isNone($role, $resource, $operation, array $params = array())
    {
        return $this->check($role, $resource, $operation, $params) === self::NONE;
    }

    public function __invoke($role, $resource, $operation, array $params = array())
    {
        return $this->check($role, $resource, $operation, $params);
    }
}