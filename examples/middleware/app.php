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
		if ($req->path() == 'null') {
			return;
		} else if ($req->path() == 'true') {
			$res->text('Success');
			return true;
		} else if ($req->path() == 'false') {
			return false;
		} else if ($req->path() == 'error') {
			throw new \Exception('OH NO!');
		}
		return $res->text('Try /null, /true, /false or /error.');	
	})
	->add(function(Request $req, Response $res, App $app) {
		return $res->text('Second middleware.');	
	})
	->notFoundHandler(function(Request $req, Response $res, App $app) {
		$res->status(404)
			->text("Not Found");
	})
	->errorHandler(function(Request $req, Response $res, App $app, Exception $e) {
		$res->status(500)
			->text("Error: {$e->getMessage()}");
	});

$app->execute((new Request())->import())->export();