<?php
use Js\App,
	Js\App\Request, 
	Js\App\Response;

chdir(__DIR__);
require 'vendor/autoload.php';

$app = new App();
$app->add(function(Request $req, Response $res, App $app) {
		if ($req->getPath() == 'null') {
			return;
		} else if ($req->getPath() == 'true') {
			$res->text('Success');
			return true;
		} else if ($req->getPath() == 'false') {
			return false;
		} else if ($req->getPath() == 'error') {
			throw new \Exception('OH NO!');
		}
		return $res->text('Try accessing /null, /true, /false or /error.');	
	})
	->add(function(Request $req, Response $res, App $app) {
		return $res->text('Second middleware.');	
	})
	->notFoundHandler(function(Request $req, Response $res, App $app) {
		$res->setStatus(404)
			->text("Not Found");
	})
	->errorHandler(function(Request $req, Response $res, App $app, Exception $e) {
		$res->setStatus(500)
			->text("Error: {$e->getMessage()}");
	});

$app->execute((new Request())->import())->export();