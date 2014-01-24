# JS Framework

## Hello World

app.php

	use Js\App,
		Js\App\Request, 
		Js\App\Response;
	
	require 'vendor/autoload.php';

	$app = new App();
	$app->add(function(Request $req, Response $res, App $app) {
		return $res->text('Hello World');
	});
	
	$app->execute((new Request())->import())->export();
	
Run:

	php -S localhost:8000 app.php
	
And load it up in the browser at [http://localhost:8000/](http://localhost:8000/).
	
What's happening here:

1. Include the [Composer](https://getcomposer.org/) autoloader. Js\App does not perform any class loading and does not include an autoloader. You can use any class loader you like (or roll your own), but I highly reccomend Composer's.
2. Initialise a `Js\App` object.
3. Add some [middlewear](http://en.wikipedia.org/wiki/Middleware) to handle the request.
4. Call `execute` to run the application. This method expects a `Js\App\Request` object, and returns a `Js\App\Response` object. The `import` method grabs all of the request data from PHP's globals, and the `export` method sends the response data back out. It is also possible to skip these methods and construct the request data manually, which is useful for testing.

In this example we're sending a `text/plain` response, the following methods are also avaliable:	

### HTML
		
	$res->html('<p>Hello World</p>');
	
### JSON
	
	$res->json(['Hello World']);
	
### XML
	
	$dom = new \DOMDocument();
	$dom->appendChild($dom->createElement('hello-world'));
	$res->xml($dom);
	
### Other
	
	$res->setHeader('Content-Type', 'application/pdf')->setBody($data);

## Middleware

Middleware return values will be handeled as follows:

* **null**  
	The request has not been completed, run further middleware.
* **true** (or a value that will be cast to true)  
	The request has been completed successfully, do not run any further middleware.
* **false** (or a value that will be cast to false)  
	The request has been completed unsuccessfully, do not run any further middleware, call the app's not found handler.
	
If no middleware completes the request the not found handler will be called:

	$app->notFoundHandler(function(Request $req, Response $res, App $app) {
		$res->setStatus(404)
			->text("Not Found");
	});

Any uncaught exceptions thrown during execution of middleware will be refered to the app's error handler:

	$app->errorHandler(function(Request $req, Response $res, App $app, Exception $e) {
		$app->error($e);
		$res->setStatus(500)
			->text("Error: {$e->getMessage())}");
	});

### File and Directory Paths

If you're using relative paths you should always ensure that the current working directory is correct by calling `chdir` at the top of app.php:

	chdir(__DIR__);
