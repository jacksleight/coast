<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

use Coast\App,
    Coast\App\Request,
    Coast\App\Response,
    Coast\App\Access,
    Coast\App\Executable,
    Coast\App\Exception,
    Coast\Options;

class SubApp implements Access, Executable
{
    use Access\Implementation;
    use Options;

    protected $_subApp;

    public function __construct(App $subApp, array $options = array())
    {
        $this->_subApp = $subApp;
        $this->options(array_merge([
            'path' => 'admin'
        ], $options));
    }

    public function execute(Request $req, Response $res)
    {
        $parts = explode('/', $req->path());
        $path  = array_shift($parts);
        if ($path != $this->_options->path) {
            return;
        }

        $req->path(implode('/', $parts));
        $result = $this->_subApp->execute($req, $res);
        $req->path(implode('/', array_merge([$path], $parts)));

        return $result;
    }
}