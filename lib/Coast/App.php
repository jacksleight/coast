<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\App\Request,
    Coast\App\Response,
    Coast\App\Executable,
    Coast\App\Exception,
    Coast\File;

/**
 * Coast application object.
 */
class App implements Executable
{
    use \Coast\Options;
    
    const MODE_CLI  = 'cli';
    const MODE_HTTP = 'http';
    
    /**
     * Root directory.
     * @var Coast\Dir
     */
    protected $_dir;
    
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
     * @param array $opts Options.
     * @param array $envs Additional environment variables.
     */
    public function __construct($dir, array $opts = array(), array $envs = array())
    {
        $this->_dir = new \Coast\Dir("{$dir}");

        $this->opts(array_merge([
            'path' => null,
        ], $opts));

        $this->_envs = array_merge(array(
            'MODE' => isset($_SERVER['HTTP_HOST']) ? self::MODE_HTTP : self::MODE_CLI,
        ), $_ENV, $envs);

        date_default_timezone_set('UTC');
        $this->set('app', $this);
    }

    /**
     * Require a file without leaking variables into the global scope.
     * @param  mixed   $file
     * @return mixed
     */
    public function import($_file, array $_vars = array())
    {
        $_file = !$_file instanceof File
            ? new \Coast\File("{$_file}")
            : $_file;
        $_file = $_file->isRelative()
            ? $this->app->dir()->file($_file)
            : $_file;
        return \Coast\import($_file, array_merge(['app' => $this], $_vars));
    }

    /**
     * Get root directory.
     * @return Coast\Dir
     */
    public function dir()
    {
        return $this->_dir;
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
        if (isset($value)) {
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
        if (isset($params)) {
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

        if (isset($this->_opts->path)) {
            if (!preg_match('/^(' . preg_quote($this->_opts->path, '/') . ')(?:\/(.*))?$/', $req->path(), $path)) {
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

        if (isset($this->_opts->path)) {
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
     * Attempts to call the `call` method of the parameter named `$name`
     * @param string $name
     * @param array $args
     */
    public function __call($name, array $args)
    {
        $value = $this->get($name);
        if (!is_object($value) || !method_exists($value, 'call')) {
            throw new Exception("Param '{$name}' is not an object or does not have a call method");
        }
        return call_user_func_array(array($value, 'call'), $args);
    }
}