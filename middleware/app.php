<?php
use Coast\App,
    Coast\Request, 
    Coast\Response;

date_default_timezone_set('UTC');
require __DIR__ . '/../autoload.php';

$app = new App(__DIR__);
$app->executable(function(Request $req, Response $res) {
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
    ->executable(function(Request $req, Response $res) {
        return $res->text('Second middleware.');    
    })
    ->notFoundHandler(function(Request $req, Response $res) {
        $res->status(404)
            ->text("Not Found");
    })
    ->errorHandler(function(Request $req, Response $res, Exception $e) {
        $res->status(500)
            ->text("Error: {$e->getMessage()}");
    });

$app->execute();