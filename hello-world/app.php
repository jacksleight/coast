<?php
use Coast\App,
    Coast\Request, 
    Coast\Response;

date_default_timezone_set('UTC');
require __DIR__ . '/vendor/autoload.php';

$app = new App(__DIR__);
$app->add(function(Request $req, Response $res) {    
        return $res->text('Hello World');
    });

$app->execute();