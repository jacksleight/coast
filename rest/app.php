<?php
use Coast\App,
    Coast\Request, 
    Coast\Response,
    Coast\Router;

date_default_timezone_set('UTC');
require __DIR__ . '/../autoload.php';

$app = new App(__DIR__);
$app->param('data', json_decode(file_get_contents($app->file('data.json')), true)) // Placeholder data
    ->param('router', new Router())
    ->executable($app->router)
    ->notFoundHandler(function(Request $req, Response $res) {
        $res->status(404)
            ->text("Not Found");
    });

$app->router
    ->all('index', '', function(Request $req, Response $res) {
        return $res->text("Try /users.");
    })
    ->get('get', '{entity}/{id:\d+}?', function(Request $req, Response $res) {
        $entity = $req->entity;
        if (!isset($this->data[$entity])) {
            return false;
        }
        $data = $this->data[$entity];
        $id   = $req->id;
        if (!isset($id)) {
            return $res->json($data);
        } else if (isset($data[$id])) {
            return $res->json($data[$id]);
        }
        return false;
    })
    ->post('post', '{entity}/{id:\d+}?', function(Request $req, Response $res) {
        return $res->json(['error' => 'Unimplemented']);
    })
    ->put('put', '{entity}/{id:\d+}?', function(Request $req, Response $res) {
        return $res->json(['error' => 'Unimplemented']);
    })
    ->delete('delete', '{entity}/{id:\d+}?', function(Request $req, Response $res) {
        return $res->json(['error' => 'Unimplemented']);
    });

$app->execute();