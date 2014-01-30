<?php
// Placeholder autoloader, allows the examples to work from the main library
require '../../lib/Coast.php';
set_include_path(get_include_path() . PATH_SEPARATOR . '../../lib');
spl_autoload_register();