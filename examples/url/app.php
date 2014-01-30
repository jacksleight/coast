<?php
use Coast\App,
    Coast\App\Request, 
    Coast\App\Response,
    Coast\App\Router,
    Coast\App\URL;

// Placeholder code, this allows PHP's CLI server to serve the example file
if (php_sapi_name() == 'cli-server' && $_SERVER['REQUEST_URI'] == '/example.png') {
    return false;
}

chdir(__DIR__);
require 'vendor/autoload.php';

$app = new App();
$app->add('router', new Router())
    ->set('url', new URL([
        'base'   => (new Request())->import()->base(), // Placeholder base URL, this would typically come from hard coded server config
        'router' => $app->router,
    ]))
    ->notFoundHandler(function(Request $req, Response $res) {
        $res->status(404)
            ->text("Not Found");
    });

$app->router
    ->all('index', '', function(Request $req, Response $res) {
        $base  = $this->url();
        $route = $this->url(['person' => 'jack'], 'team-person', true);
        $file  = $this->url->file('example.png');
        $query = $this->url->query(['page' => 1]);
        return $res->html("
            <a href='{$base}'>{$base}</a><br>
            <a href='{$route}'>{$route}</a><br>
            <a href='{$file}'>{$file}</a><br>
            <a href='{$query}'>{$query}</a><br>
        ");
    })
    ->all('team-person', 'team/{person}', function(Request $req, Response $res) {
        return $res->json($req->params());
    });

$app->execute((new Request())->import())->export();