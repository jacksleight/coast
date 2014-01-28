<?php
use Coast\App,
	Coast\App\Request, 
	Coast\App\Response,
	Coast\App\Router;

chdir(__DIR__);
require 'vendor/autoload.php';

$app = new App();
$app->add('router', new Router())
	->notFoundHandler(function(Request $req, Response $res, App $app) {
		$res->status(404)
			->text("Not Found");
	});

$app->router
	->all('index', '/', function(Request $req, Response $res, App $app) {
		return $res->text("Try /team and /team/jack-sleight.");
	})
	->all('team', '/team', function(Request $req, Response $res, App $app) {
		return $res->json($req->params());
	})
	->all('team-person', '/team/{person}', function(Request $req, Response $res, App $app) {
		return $res->json($req->params());
	});

$app->execute((new Request())->import())->export();