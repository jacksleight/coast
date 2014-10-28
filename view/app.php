<?php
use Coast\App,
    Coast\Request, 
    Coast\Response,
    Coast\App\View;

chdir(__DIR__);
require 'vendor/autoload.php';

$app = new App(__DIR__);
$app->set('view', new View([
		'index' => $app->dir('views'),
		'other' => $app->dir('views-other'),
	]))
    ->add(function(Request $req, Response $res) {
        return $res->html($this->view->render('/index'));
    });

$app->execute();