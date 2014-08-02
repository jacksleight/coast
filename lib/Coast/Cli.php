<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Cli
{
    const TYPE_STRING  = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';

    protected $_opts = [];
    protected $_args = [];

    public function __construct($opts = '', array $longopts = array())
    {
        global $argv;
        $this->_opts = getopt($opts, $longopts);
        $this->_args = isset($argv) ? $argv : [];
    }

    public function opts()
    {
        return $this->_opts;
    }

    public function opt($name, $type = self::TYPE_STRING)
    {
        return $this->_parseValue($this->_opts[$name], $type);
    }

    public function args()
    {
        return $this->_args;
    }

    public function arg($i, $type = self::TYPE_STRING)
    {
        return $this->_parseValue($this->_args[$i], $type);
    }

    public function prompt($value, $line = false, $type = self::TYPE_STRING)
    {
        fwrite(STDOUT, $value . ($line ? PHP_EOL : null));
        return $this->_parseValue(fgets(STDIN), $type);
    }

    public function output($value, $line = false)
    {
        fwrite(STDOUT, $value . ($line ? PHP_EOL : null));
        return $this;
    }

    public function error($value, $line = false, $exit = true)
    {
        fwrite(STDERR, $value . ($line ? PHP_EOL : null));
        if ($exit) {
            exit(0);
        }
        return $this;
    }

    public function execute($cmd)
    {
        $value = [];
        exec($cmd, $value);
        return $value;
    }

    protected function _parseValue($value, $type)
    {
        $value = trim($value);
        if (strlen($value) == 0) {
            $value = null;
        } else if ($type == self::TYPE_INTEGER) {
            $value = (int) $value;
        } else if ($type == self::TYPE_BOOLEAN) {
            $value = !in_array(strtolower($value), ['0', 'n', 'no', 'off', 'false']);
        }
        return $value;
    }
}