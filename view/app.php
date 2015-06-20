<?php
use Coast\App,
    Coast\Request, 
    Coast\Response,
    Coast\App\View;

date_default_timezone_set('UTC');
require __DIR__ . '/../autoload.php';

$app = new App(__DIR__);
$app->param('view', new View([
		'dirs' => [
			'index' => $app->dir('views'),
			'other' => $app->dir('views-other'),
		],
	]))
    ->executable($app->view);

$app->execute();