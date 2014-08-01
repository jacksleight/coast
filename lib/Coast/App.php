<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\App\Request,
    Coast\App\Exception,
    Coast\App\Executable,
    Coast\App\Response,
    Coast\Dir,
    Coast\File,
    Coast\Path;

/**
 * Coast application object.
 */
class App implements Executable
{   
    const MODE_CLI  = 'cli';
    const MODE_HTTP = 'http';
    
    /**
     * Base directory.
     * @var Coast\Dir
     */
    protected $_baseDir;
    
    /**
     * Path.
     * @var Coast\Path
     */
    protected $_path;
    
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
     * Middleware stack.
     * @var array
     */
    protected $_stack = [];

    /**
     * Handler for requests that are not handled by middleware.
     * @var Closure
     */
    protected $_notFoundHandler;

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
    public function __construct($baseDir, array $envs = array())
    {
        $this->baseDir(!$baseDir instanceof Dir
            ? new Dir("{$baseDir}")
            : $baseDir);

        $this->_envs = array_merge(array(
            'MODE' => php_sapi_name() == 'cli' ? self::MODE_CLI : self::MODE_HTTP,
        ), $_ENV, $envs);
        
        $this->set('app', $this);
    }

    /**
     * Get/set base directory.
     * @return Coast\Dir
     */
    public function baseDir(\Coast\Dir $baseDir = null)
    {
        if (func_num_args() > 0) {
            $this->_baseDir = $baseDir;
            return $this;
        }
        return $this->_baseDir;
    }

    /**
     * Get child directory.
     * @return Coast\Dir
     */
    public function dir($path = null, $create = false)
    {
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
        return $this->_baseDir->file($path);
    }

    /**
     * Require a file without leaking variables into the global scope.
     * @param  mixed   $file
     * @return mixed
     */
    public function import(File $_file, array $_vars = array())
    {
        return \Coast\import($_file, array_merge(['app' => $this], $_vars));
    }

    /**
     * Get/set root path.
     * @param  Coast\Path $name
     * @return mixed
     */
    public function path(Path $path = null)
    {
        if (func_num_args() > 0) {
            $this->_path = $path;
            return $this;
        }
        return $this->_path;
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
     * Set a parameter.
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function set($name, $value)
    {
        return $this->param($name, $value);
    }

    /**
     * Get a parameter.
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->param($name);
    }

    /**
     * Check if a parameter exists.
     * @param  string  $name
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->_params[$name]);
    }

    /**
     * Add middleware to the stack.
     * @param string $name
     * @param Closure|Coast\App\Executable $value
     * @return self
     */
    public function add($name, $value = null)
    {
        if (!isset($value)) {
            $value = $name;
            $name  = null;
        }
        if (!$value instanceof \Closure && !$value instanceof Executable) {
            throw new Exception("Param '{$name}' is not a closure or instance of Coast\App\Executable");
        }
        array_push($this->_stack, $value instanceof \Closure
            ? $value->bindTo($this)
            : [$value, 'execute']);
        if (isset($name)) {
            $this->set($name, $value);
        }
        return $this;
    }

    /**
     * Execute the application, running middleware in order.
     * @param  Coast\App\Request $req Request object.
     * @param  Coast\App\Response $res Response object.
     */
    public function execute(Request $req = null, Response $res = null)
    {
        $auto = false;
        if (!isset($req)) {
            $auto = true;
            $req  = (new Request())->fromGlobals();
            $res  = (new Response($req));
        } else if (!isset($res)) {
            throw new Exception('You must pass a Response object when passing a Request object');
        }

        if (isset($this->_path)) {
            if (!preg_match('/^(' . preg_quote((string) $this->_path, '/') . ')(?:\/(.*))?$/', $req->path(), $path)) {
                return null;
            }
            $base = $req->base();
            $req->base("{$base}{$path[1]}/")
                ->path(isset($path[2]) ? $path[2] : '');
        }

        $this->set('req', $req)
             ->set('res', $res);
        try {
            $result = null;
            foreach($this->_stack as $item) {
                $result = call_user_func($item, $req, $res);
                if (isset($result)) {
                    break;
                }
            }
            if ((bool) $result !== true) {
                if (isset($this->_notFoundHandler)) {
                    $result = call_user_func($this->_notFoundHandler, $req, $res);
                } else {
                    throw new Exception('Nothing successfully handled the request');
                }
            }
        } catch (\Exception $e) {
            if (isset($this->_errorHandler)) {
                $result = call_user_func($this->_errorHandler, $req, $res, $e);
            } else {
                throw $e;
            }
        }
        $this->set('req', null)
             ->set('res', null);

        if (isset($this->_path)) {
            $req->base($base)
                ->path($path[0]);
        }
        
        if ($auto) {
            $res->toGlobals();
        } else {
            return $result;
        }
    }

    /**
     * Set the not found handler
     * @param  Closure $notFoundHandler
     * @return self
     */
    public function notFoundHandler(\Closure $notFoundHandler)
    {
        $this->_notFoundHandler = $notFoundHandler->bindTo($this);
        return $this;
    }

    /**
     * Set the error handler
     * @param  Closure $errorHandler
     * @return self
     */
    public function errorHandler(\Closure $errorHandler)
    {
        $this->_errorHandler = $errorHandler->bindTo($this);
        return $this;
    }

    /**
     * Alias of `set`
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Alias of `get`
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }
    
    /**
     * Alias of `has`
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Attempts to call parameter named `$name`
     * @param string $name
     * @param array $args
     */
    public function __call($name, array $args)
    {
        $value = $this->get($name);
        if (!is_callable($value)) {
            throw new \Coast\App\Exception("Param '{$name}' is not callable");
        }
        return call_user_func_array($value, $args);
    }
}