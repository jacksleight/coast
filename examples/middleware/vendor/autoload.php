<?php
// This file is a hack to allow the examples to work from the main library.
// Don't do this, use Composer's autoloader instead, as per the example in /README.md.

require '../../lib/Coast.php';
set_include_path(get_include_path() . PATH_SEPARATOR . '../../lib');
spl_autoload_register();