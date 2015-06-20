<?php
use Coast\App,
    Coast\Request, 
    Coast\Response,
    Coast\Sitemap;

date_default_timezone_set('UTC');
require __DIR__ . '/../autoload.php';

$app = new App(__DIR__);
$app->executable(function(Request $req, Response $res) {    
	$sitemap = new Sitemap();
	$sitemap->add(
		new \Coast\Url('http://coastphp.com/'),
		new \DateTime(),
		Sitemap::CHANGEFREQ_WEEKLY,
		1
	);
    return $res->xml($sitemap->toXml());
});

$app->execute();