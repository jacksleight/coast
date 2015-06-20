<?php
spl_autoload_register(function($class) {
	require __DIR__ . '/../dev/lib/' . str_replace(['Coast', '\\'], ['', '/'], $class) . '.php';
});
require __DIR__ . '/../dev/vendor/autoload.php';
require __DIR__ . '/../dev/lib/Coast.php';