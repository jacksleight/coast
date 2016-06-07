<?php
/* 
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

use Coast\Request;
use Coast\Response;

interface Executable
{
    public function execute(Request $req, Response $res);

    public function preExecute(Request $req, Response $res);

    public function postExecute(Request $req, Response $res);
}