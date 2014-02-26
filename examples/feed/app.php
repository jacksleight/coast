<?php
use Coast\App,
    Coast\App\Request, 
    Coast\App\Response,
    Coast\Feed;

chdir(__DIR__);
require 'vendor/autoload.php';

$app = new App();
$app->add(function(Request $req, Response $res) {    
	$feed = new Feed\Atom(
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
    return $res->xml($feed, 'atom');
});

$app->execute((new Request())->import())->export();