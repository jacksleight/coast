<?php
use Coast\App,
    Coast\Request, 
    Coast\Response,
    Coast\Router,
    Coast\UrlResolver;

// Placeholder code, this allows PHP's CLI server to serve the example file
if (php_sapi_name() == 'cli-server' && $_SERVER['REQUEST_URI'] == '/example.png') {
    return false;
}

date_default_timezone_set('UTC');
require __DIR__ . '/../autoload.php';

$app = new App(__DIR__);
$app->param('router', new Router())
    ->executable($app->router)
    ->param('url', new UrlResolver([
        'baseUrl'     => new \Coast\Url((new Request())->fromGlobals()->base()),
        'baseDir'     => $app->dir(),
        'router'      => $app->router,
    ]))
    ->notFoundHandler(function(Request $req, Response $res) {
        $res->status(404)
            ->text("Not Found");
    });

$app->router
    ->all('index', '', function(Request $req, Response $res) {
        $base  = $this->url();
        $route = $this->url(['person' => 'jack'], 'team-person', true);
        $dir   = $this->url->dir('images');
        $file  = $this->url->file('example.png');
        $query = $this->url->query(['page' => 1]);
        return $res->html("
            <a href='{$base}'>{$base}</a><br>
            <a href='{$route}'>{$route}</a><br>
            <a href='{$dir}'>{$dir}</a><br>
            <a href='{$file}'>{$file}</a><br>
            <a href='{$query}'>{$query}</a><br>
        ");
    })
    ->all('team-person', 'team/{person}', function(Request $req, Response $res) {
        return $res->json($req->params());
    });

$app->execute();