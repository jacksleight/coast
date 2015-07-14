<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
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

    public function execute(Request $req = null, Response $res = null)
    {
        if (!preg_match($this->_regex, $req->path(), $path)) {
            return;
        }
        $base = $req->base();
        $req->base("{$base}{$path[1]}/")
            ->path(isset($path[2]) ? $path[2] : '');

        $result = $this->_executable->execute($req, $res);

        $req->base($base)
            ->path($path[0]);
 
        return $result;
    }
}