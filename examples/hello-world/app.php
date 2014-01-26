<?php
use Coast\App,
	Coast\App\Request, 
	Coast\App\Response;

chdir(__DIR__);
require 'vendor/autoload.php';

$app = new App();
$app->add(function(Request $req, Response $res, App $app) {
		return $res->text('Hello World');
	});

$app->execute((new Request())->import())->export();