<?php
use Coast\App,
    Coast\Request, 
    Coast\Response,
    Coast\Feed;

date_default_timezone_set('UTC');
require __DIR__ . '/../autoload.php';

$app = new App(__DIR__);
$app->executable(function(Request $req, Response $res) {    
	$feed = new Feed(
		'Coast',
		new \Coast\Url('http://coastphp.com/'),
		'Jack Sleight',
		new \DateTime()
	);
	$feed->add(
		'Example Article',
		new \Coast\Url('http://coastphp.com/example-article'),
		new \DateTime(),
		'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eum, quasi, voluptas, tenetur provident eaque numquam voluptatum placeat cum quod expedita sapiente corporis vitae eligendi velit quam ipsam aspernatur aperiam cupiditate.'
	);
    return $res->xml($feed->toXml(), 'atom');
});

$app->execute();