<?php
use Coast\App,
    Coast\Request, 
    Coast\Response;

date_default_timezone_set('UTC');
require __DIR__ . '/../autoload.php';

$app = new App(__DIR__);
$app->executable(function(Request $req, Response $res) {    
        return $res->text('Hello World');
    });

$app->execute();