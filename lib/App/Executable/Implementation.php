<?php
/* 
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App\Executable;

use Coast\Request;
use Coast\Response;

trait Implementation
{
    public function execute(Request $req, Response $res)
    {}

    public function preExecute(Request $req, Response $res)
    {}

    public function postExecute(Request $req, Response $res)
    {}
}