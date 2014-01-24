<?php
/* 
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\App;

interface Access
{
	public function setApp(\Js\App $app);

	public function __get($name);

	public function __isset($name);

	public function __call($name, array $args);
}