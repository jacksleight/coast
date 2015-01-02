<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Cli
{
    protected $_inputStream;

    protected $_outputStream;

    protected $_errorStream;

    protected $_params = [];

    protected $_args = [];

    protected $_opts = [];

    public function __construct($stdin = null, $stdout = null, $stderr = null)
    {
        $cli = php_sapi_name() == 'cli';
        if (!$cli) {
            header('Content-Type: text/plain');
        }

        $this->_inputStream = isset($stdin)
            ? $stdin
            : fopen($cli ? 'php://stdin' : 'php://input', 'r');
        $this->_outputStream = isset($stdout)
            ? $stdout
            : fopen($cli ? 'php://stdout' : 'php://output', 'w');
        $this->_errorStream = isset($stderr)
            ? $stderr
            : fopen($cli ? 'php://stderr' : 'php://output', 'w');
    }

    public function fromGlobals($opts = '', array $longopts = array())
    {
        $this->params(isset($_GET) ? $_GET : []);

        global $argv;
        $opts = getopt($opts, $longopts);
        $this->args(isset($argv) ? $argv : []);
        $this->opts($opts ? $opts : []);  
        
        return $this;
    }

    public function param($name, $value = null)
    {
        if (func_num_args() > 1) {
            $this->_params[$name] = $value;
            return $this;
        }
        return isset($this->_params[$name])
            ? $this->_params[$name]
            : null;
    }

    public function params(array $params = null)
    {
        if (func_num_args() > 0) {
            foreach ($params as $name => $value) {
                $this->param($name, $value);
            }
            return $this;
        }
        return $this->_params;
    }

    public function arg($i, $value = null)
    {
        if (func_num_args() > 1) {
            $this->_args[$i]   = $value;
            $this->_params[$i] = &$this->_args[$i];
            return $this;
        }
        return isset($this->_args[$i])
            ? $this->_args[$i]
            : null;
    }

    public function args(array $args = null)
    {
        if (func_num_args() > 0) {
            foreach ($args as $i => $value) {
                $this->arg($i, $value);
            }
            return $this;
        }
        return $this->_args;
    }

    public function opt($name, $value = null)
    {
        if (func_num_args() > 1) {
            $this->_opts[$name]   = $value;
            $this->_params[$name] = &$this->_opts[$name];
            return $this;
        }
        return isset($this->_opts[$name])
            ? $this->_opts[$name]
            : null;
    }

    public function opts(array $opts = null)
    {
        if (func_num_args() > 0) {
            foreach ($opts as $name => $value) {
                $this->opt($name, $value);
            }
            return $this;
        }
        return $this->_opts;
    }

    public function input()
    {
        return fgets($this->_inputStream);
    }

    public function output($value, $line = false)
    {
        fwrite($this->_outputStream, $value . ($line ? PHP_EOL : null));
        return $this;
    }

    public function error($value, $line = false, $exit = true)
    {
        fwrite($this->_errorStream, $value . ($line ? PHP_EOL : null));
        if ($exit) {
            exit(0);
        }
        return $this;
    }

    public function prompt($value, $line = false)
    {
        fwrite($this->_outputStream, $value . ($line ? PHP_EOL : null));
        return trim(fgets($this->_inputStream));
    }

    public function execute($cmd)
    {
        $value = [];
        exec($cmd, $value);
        return $value;
    }

    public function __set($name, $value)
    {
        $this->param($name, $value);
    }

    public function __get($name)
    {
        return $this->param($name);
    }

    public function __isset($name)
    {
        return $this->param($name) !== null;
    }

    public function __unset($name)
    {
        $this->param($name, null);
    }
}