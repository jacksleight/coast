<?php
/* 
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Router;

interface Routable
{
    public function route(\Coast\Request $req, \Coast\Response $res, array $route);
}