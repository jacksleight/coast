<?php
use Coast\App,
	Coast\App\Request, 
	Coast\App\Response,
	Coast\App\Router;

// You should use Composer's autoloader, as per the example in /README.md
chdir(__DIR__);
require '../../lib/Coast.php';
set_include_path(get_include_path() . PATH_SEPARATOR . '../../lib');
spl_autoload_register();

$app = new App();
$app->add('router', new Router())
	->notFoundHandler(function(Request $req, Response $res, App $app) {
		$res->status(404)
			->text("Not Found");
	});

$app->router
	->get('index', '/', function(Request $req, Response $res, App $app) {
		return $res->text("Try /users, /users/add or /users/edit/1.");
	})
	->get('user', '/users/{action}?/{id:\d+}?', function(Request $req, Response $res, App $app) {
		return $res->json($req->params());
	});

$app->execute((new Request())->import())->export();