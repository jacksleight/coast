<?php
/* 
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

interface Access
{
    public function app(\Coast\App $app);

    public function __get($name);

    public function __set($name, $value);

    public function __isset($name);

    public function __unset($name);

    public function __call($name, array $args);
}