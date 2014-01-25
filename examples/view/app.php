<?php
use Js\App,
	Js\App\Request, 
	Js\App\Response,
	Js\App\View;

chdir(__DIR__);
require 'vendor/autoload.php';

$app = new App();
$app->set('view', new View(['dir' => 'views']))
	->add(function(Request $req, Response $res, App $app) {
		return $res->html($app->view->render('/index'));
	});

$app->execute((new Request())->import())->export();