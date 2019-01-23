<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\App\Exception;
use Coast\App\Executable;
use Coast\Dir;
use Coast\File;
use Coast\Lazy;
use Coast\Path;
use Coast\Request;
use Coast\Response;
use Closure;

/**
 * Coast application object.
 */
class App implements Executable
{   
    use Executable\Implementation;

    const MODE_CLI  = 'cli';
    const MODE_HTTP = 'http';
    
    /**
     * Base directory.
     * @var Coast\Dir
     */
    protected $_baseDir;

    /**
     * Is subapp.
     * @var boolean
     */
    protected $_isSubapp = false;
       
    /**
     * Environment variables.
     * @var array
     */
    protected $_envs = [];

    /**
     * Parameters.
     * @var array
     */
    protected $_params = [];

    /**
     * Executables stack.
     * @var array
     */
    protected $_executables = [];

    /**
     * Handler for requests that are not handled by middleware.
     * @var Closure
     */
    protected $_failureHandler;

    /**
     * Handler for errors thrown in middleware.
     * @var Closure
     */
    protected $_errorHandler;

    /**
     * Construct a new Coast application.
     * @param mixed $baseDir Base directory.
     * @param array $envs Additional environment variables.
     */
    public function __construct($baseDir = null, array $envs = array())
    {
        $this->baseDir($baseDir);
        $this->_envs = array_merge(array(
            'MODE' => php_sapi_name() == 'cli' ? self::MODE_CLI : self::MODE_HTTP,
        ), $_ENV, $envs);
        $this->param('app', $this);
    }

    /**
     * Get/set base directory.
     * @return Coast\Dir
     */
    public function baseDir($baseDir = null)
    {
        if (func_num_args() > 0) {
            $baseDir = isset($baseDir) && !$baseDir instanceof Dir
                ? new Dir("{$baseDir}")
                : $baseDir;
            $this->_baseDir = $baseDir;
            return $this;
        }
        return $this->_baseDir;
    }

    /**
     * Get/set is subapp.
     * @return boolean
     */
    public function isSubapp($isSubapp = null)
    {
        if (func_num_args() > 0) {
            $this->_isSubapp = $isSubapp;
            return $this;
        }
        return $this->_isSubapp;
    }

    /**
     * Get child directory.
     * @return Coast\Dir
     */
    public function dir($path = null, $create = false)
    {
        if (!isset($this->_baseDir)) {
            throw new App\Exception('Base directory not set');
        }
        return isset($path)
            ? $this->_baseDir->dir($path, $create)
            : $this->_baseDir;
    }

    /**
     * Get child file.
     * @return Coast\File
     */
    public function file($path)
    {
        if (!isset($this->_baseDir)) {
            throw new App\Exception('Base directory not set');
        }
        return $this->_baseDir->file($path);
    }

    /**
     * Load a file without leaking variables, include app object in vars.
     * @param  mixed   $file
     * @param  array   $vars
     * @return mixed
     */
    public function load($file, array $vars = array())
    {
        $file = !$file instanceof File
            ? new File("{$file}")
            : $file;
        $file = $file->isRelative()
            ? $this->file($file)
            : $file;
        return \Coast\load($file, array_merge(['app' => $this], $vars));
    }

    /**
     * Lazy load a file without leaking variables, include app object in vars.
     * @param  mixed   $file
     * @param  array   $vars
     * @return mixed
     */
    public function lazy($source, array $vars = array())
    {
        if (is_string($source)) {
            $source = new File("{$source}");
            $source = $source->isRelative()
                ? $this->file($source)
                : $source;
        }
        return new Lazy($source, array_merge(['app' => $this], $vars));
    }

    /**
     * Get environment variables.
     * @param  string $name
     * @return mixed
     */
    public function env($name)
    {
        return isset($this->_envs[$name])
            ? $this->_envs[$name]
            : null;
    }

    /**
     * Get the mode (HTTP or CLI).
     * @return string
     */
    public function mode()
    {
        return $this->env('MODE');
    }

    /**
     * Is mode HTTP.
     * @return bool
     */
    public function isHttp()
    {
        return $this->mode() == self::MODE_HTTP;
    }

    /**
     * Is mode CLI.
     * @return bool
     */
    public function isCli()
    {
        return $this->mode() == self::MODE_CLI;
    }

    /**
     * Set/get param.
     * @param  string $name  
     * @param  mixed $value
     * @return self|mixed
     */
    public function param($name, $value = null)
    {
        if (func_num_args() > 1) {
            if ($value instanceof \Coast\App\Access) {
                $value->app($this);
            }
            $this->_params[$name] = $value;
            return $this;
        }
        return isset($this->_params[$name])
            ? $this->_params[$name]
            : null;
    }

    /**
     * Set/get multiple params.
     * @param  array $params
     * @return self|array
     */
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

    /**
     * Add executable to the stack.
     * @param string $name
     * @param Closure|Coast\App\Executable $value
     * @return self
     */
    public function executable($executable, $subpath = null)
    {
        if (!$executable instanceof Closure && !$executable instanceof Executable) {
            throw new App\Exception("Object is not a closure or instance of Coast\App\Executable");
        }
        if (isset($subpath)) {
            $executable = new App\Subpath($executable, $subpath);
        } else if ($executable instanceof App || $executable instanceof Lazy) {
            $executable->isSubapp(true);
        }
        array_push($this->_executables, $executable instanceof Closure
            ? $executable->bindTo($this)
            : $executable);
        return $this;
    }

    /**
     * Execute the application, running middleware in order.
     * @param  Coast\Request $req Request object.
     * @param  Coast\Response $res Response object.
     */
    public function execute(Request $req = null, Response $res = null)
    {
        $auto = false;
        if (!isset($req)) {
            $auto = true;
            $req  = (new Request())->fromGlobals();
            $res  = (new Response($req));
        } else if (!isset($res)) {
            throw new App\Exception('You must pass a Response object when passing a Request object');
        }

        $this->param('req', $req)
             ->param('res', $res);
        try {
            $this->preExecute($req, $res);
            foreach ($this->_executables as $executable) {
                if ($executable instanceof Executable && !$executable instanceof App) {
                    $executable->preExecute($req, $res);
                }
            }
            $result = null;
            foreach ($this->_executables as $executable) {
                $result = call_user_func($executable instanceof Executable
                    ? [$executable, 'execute']
                    : $executable, $req, $res);
                if (isset($result)) {
                    break;
                }
            }
            if (!$result && !$this->_isSubapp) {
                if (isset($this->_failureHandler)) {
                    $result = call_user_func($this->_failureHandler, $req, $res);
                } else {
                    throw new App\Exception('Nothing successfully handled the request');
                }
            }
            foreach ($this->_executables as $executable) {
                if ($executable instanceof Executable && !$executable instanceof App) {
                    $executable->postExecute($req, $res);
                }
            }
            $this->postExecute($req, $res);
        } catch (\Exception $e) {
            if (isset($this->_errorHandler)) {
                $result = call_user_func($this->_errorHandler, $req, $res, $e);
            } else {
                throw $e;
            }
        }
        $this->param('req', null)
             ->param('res', null);
        
        if ($auto) {
            $res->toGlobals();
        } else {
            return $result;
        }
    }

    /**
     * Set the failure handler
     * @param  Closure $failureHandler
     * @return self
     */
    public function failureHandler(Closure $failureHandler)
    {
        $this->_failureHandler = $failureHandler->bindTo($this);
        return $this;
    }

    /**
     * Set the error handler
     * @param  Closure $errorHandler
     * @return self
     */
    public function errorHandler(Closure $errorHandler)
    {
        $this->_errorHandler = $errorHandler->bindTo($this);
        return $this;
    }

    /**
     * Set a parameter.
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function __set($name, $value)
    {
        return $this->param($name, $value);
    }

    /**
     * Get a parameter.
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        $value = $this->param($name);
        return $value;
    }

    /**
     * Check if a parameter exists.
     * @param  string  $name
     * @return boolean
     */
    public function __isset($name)
    {
        return $this->param($name) !== null;
    }

    /**
     * Unset a parameter.
     * @param  string  $name
     * @return boolean
     */
    public function __unset($name)
    {
        return $this->param($name, null);
    }

    /**
     * Attempts to call parameter named `$name`
     * @param string $name
     * @param array $args
     */
    public function __call($name, array $args)
    {
        $value = $this->param($name);
        if (!is_callable($value)) {
            throw new \Coast\App\Exception("Param '{$name}' is not callable");
        }
        return call_user_func_array($value, $args);
    }
}