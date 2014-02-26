<?php
use Coast\App,
    Coast\App\Request, 
    Coast\App\Response,
    Coast\Sitemap;

chdir(__DIR__);
require 'vendor/autoload.php';

$app = new App();
$app->add(function(Request $req, Response $res) {    
	$sitemap = new Sitemap();
	$sitemap->add(
		new \Coast\Url('http://coastphp.com/'),
		new \DateTime(),
		Sitemap::CHANGES_WEEKLY,
		1
	);
    return $res->xml($sitemap->xml());
});

$app->execute((new Request())->import())->export();