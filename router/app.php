<?php
use Coast\App,
    Coast\Request, 
    Coast\Response,
    Coast\App\Router;

date_default_timezone_set('UTC');
require __DIR__ . '/../autoload.php';

$app = new App(__DIR__);
$app->param('router', new Router())
    ->executable($app->router)
    ->notFoundHandler(function(Request $req, Response $res) {
        $res->status(404)
            ->text("Not Found");
    });

$app->router
    ->all('index', '', function(Request $req, Response $res) {
        return $res->text("Try /team and /team/jack.");
    })
    ->all('team', 'team', function(Request $req, Response $res) {
        return $res->json($req->params());
    })
    ->all('team-person', 'team/{person}', function(Request $req, Response $res) {
        return $res->json($req->params());
    });

$app->execute();