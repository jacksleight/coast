<?php
use Coast\App,
	Coast\App\Request, 
	Coast\App\Response;

// You should use Composer's autoloader, as per the example in /README.md
chdir(__DIR__);
require '../../lib/Coast.php';
set_include_path(get_include_path() . PATH_SEPARATOR . '../../lib');
spl_autoload_register();

$app = new App();
$app->add(function(Request $req, Response $res, App $app) {
		return $res->text('Hello World');
	});

$app->execute((new Request())->import())->export();