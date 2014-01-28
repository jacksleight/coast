<?php
use Coast\App,
	Coast\App\Request, 
	Coast\App\Response,
	Coast\App\Router;

chdir(__DIR__);
require 'vendor/autoload.php';

$app = new App();
$app->set('data', json_decode(file_get_contents('data.json'), true)) // Dummy data
	->add('router', new Router())
	->notFoundHandler(function(Request $req, Response $res, App $app) {
		$res->status(404)
			->text("Not Found");
	});

$app->router
	->all('index', '/', function(Request $req, Response $res, App $app) {
		return $res->text("Try /users.");
	})
	->get('get', '/{entity}/{id:\d+}?', function(Request $req, Response $res, App $app) {
		$entity	= $req->entity;
		if (!isset($app->data[$entity])) {
			return false;
		}
		$data  = $app->data[$entity];
		$id    = $req->id;
		if (!isset($id)) {
			return $res->json($data);
		} else if (isset($data[$id])) {
			return $res->json($data[$id]);
		}
		return false;
	})
	->post('post', '/{entity}/{id:\d+}?', function(Request $req, Response $res, App $app) {
		return $res->json(['error' => 'Unimplemented']);
	})
	->put('put', '/{entity}/{id:\d+}?', function(Request $req, Response $res, App $app) {
		return $res->json(['error' => 'Unimplemented']);
	})
	->delete('delete', '/{entity}/{id:\d+}?', function(Request $req, Response $res, App $app) {
		return $res->json(['error' => 'Unimplemented']);
	});

$app->execute((new Request())->import())->export();