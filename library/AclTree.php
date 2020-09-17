<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class AclTree
{ 
    const ALLOW   = true;
    const DENY    = false;
    const UNKNOWN = null;

    protected $_throwUnknown = false;
    
    protected $_roles = [];

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new \Coast\Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
    }

    public function throwUnknown($value = null)
    {
        if (func_num_args() > 0) {
            $this->_throwUnknown = $value;
            return $this;
        }
        return $this->_throwUnknown;
    }

    public function role($name, array $value = null)
    {
        if (func_num_args() > 1) {
            $value += [
                'extend' => null,
                'rules'  => [],
            ];
            $rules = $this->_normalize($value['rules']);
            if (isset($value['extend'])) {
                if (!isset($this->_roles[$value['extend']])) {
                    throw new Exception("Role '{$value['extend']}' does not exist");
                }
                $rules = array_merge($this->_roles[$value['extend']]['rules'], $rules);
            }
            $value['rules'] = $rules;
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

    public function check($role, $path, array $args = [])
    {
        if (!isset($this->_roles[$role])) {
            throw new Exception("Role '{$role}' does not exist");
        }

        $rules   = $this->_roles[$role]['rules'];
        $outcome = $this->_check($rules, $path, $args);

        if ($this->_throwUnknown && $outcome === self::UNKNOWN) {
            throw new Exception("Unknown path, ACL does not have rules matching '" . implode(':', $path) . "'");
        }

        return $outcome;
    }

    protected function _check(array $rules, array $path, array $args)
    {
        $name = array_shift($path);
        $outcome = self::UNKNOWN;
        foreach ($rules as $rule) {
            $result = self::UNKNOWN;
            list($match, $test, $subs) = $rule;
            if (preg_match($match, $name)) {
                if (count($path)) {
                    $result = $this->_check($subs, $path, $args);
                } else {
                    if (is_callable($test)) {
                        $result = call_user_func_array($test, $args);
                    } else {
                        $result = $test;
                    }
                }
                if ($result !== self::UNKNOWN) {
                    $outcome = $result;
                }
            }
        }
        return $outcome;
    }

    protected function _normalize(array $rules)
    {
        foreach ($rules as $i => $rule) {
            if (count($rule) < 3) {
                if (is_array($rule[1])) {
                    array_splice($rule, 1, 0, [null]);
                } else {
                    array_splice($rule, 2, 0, [[]]);
                }
            }
            list($match, $test, $subs) = $rule;
            $match = str_replace('*', '.*', $match);
            $match = explode('|', $match);
            $match = array_map(function($v) { return preg_quote($v, '/'); }, $match);
            $match = implode('|', $match);
            $match = "({$match})";
            $match = str_replace('\.\*', '.*', $match);
            $match = "/^{$match}$/";
            $subs = $this->_normalize($subs);
            $rules[$i] = [$match, $test, $subs];
        }
        return $rules;
    }

    public function isAllow($role, $path, array $args = [])
    {
        return $this->check($role, $path, $args) === self::ALLOW;
    }

    public function isDeny($role, $path, array $args = [])
    {
        return $this->check($role, $path, $args) === self::DENY;
    }

    public function isUnknown($role, $path, array $args = [])
    {
        return $this->check($role, $path, $args) === self::UNKNOWN;
    }

    public function __invoke($role, $path, array $args = [])
    {
        return $this->check($role, $path, $args);
    }
}