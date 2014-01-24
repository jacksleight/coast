<?php
/* 
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\App;

interface Routable
{
	public function route(\Js\App\Request $req, \Js\App\Response $res);
}