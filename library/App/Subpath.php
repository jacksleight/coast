<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

use Coast\Request,
    Coast\App\Exception,
    Coast\App\Executable,
    Coast\Response;

class Subpath implements Executable
{   
    use Executable\Implementation;
    
    protected $_executable;

    protected $_path;

    protected $_regex;

    public function __construct(Executable $executable, $path)
    {
        $this->executable($executable);
        $this->path($path);
    }

    public function executable(Executable $executable = null)
    {
        if (func_num_args() > 0) {
            if (!$executable instanceof Closure && !$executable instanceof Executable) {
                throw new App\Exception("Object is not a closure or instance of Coast\App\Executable");
            }
            $this->_executable = $executable;
            return $this;
        }
        return $this->_executable;
    }

    public function path($path = null)
    {
        if (func_num_args() > 0) {
            $this->_path  = $path;
            $this->_regex = '/^(' . preg_quote((string) $this->_path, '/') . ')(?:\/(.*))?$/';
            return $this;
        }
        return $this->_path;
    }

    public function execute(Request $req, Response $res)
    {
        if (!preg_match($this->_regex, $req->path(), $path)) {
            return;
        }
        $base = $req->base();
        $req->base("{$base}{$path[1]}/")
            ->path(isset($path[2]) ? $path[2] : '');

        $result = call_user_func($this->_executable instanceof Executable
            ? [$this->_executable, 'execute']
            : $this->_executable, $req, $res);
                
        $req->base($base)
            ->path($path[0]);
 
        return $result;
    }

    public function preExecute(Request $req, Response $res)
    {
        if (!preg_match($this->_regex, $req->path(), $path)) {
            return;
        }
        if ($this->_executable instanceof Executable) {
            $this->_executable->preExecute($req, $res);
        }
    }

    public function postExecute(Request $req, Response $res)
    {
        if (!preg_match($this->_regex, $req->path(), $path)) {
            return;
        }
        if ($this->_executable instanceof Executable) {
            $this->_executable->postExecute($req, $res);
        }
    }
}