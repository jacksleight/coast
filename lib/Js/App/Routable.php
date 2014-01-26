<?php
/* 
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Js\App;

interface Routable
{
	public function route(\Js\App\Request $req, \Js\App\Response $res);
}