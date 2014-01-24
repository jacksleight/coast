# Middleware

...

## Return Values

Middleware return values will be handeled as follows:

* **null**  
	The request has not been completed, run further middleware.
* **true** (or a value that will be cast to true)  
	The request has been completed successfully, do not run any further middleware.
* **false** (or a value that will be cast to false)  
	The request has been completed unsuccessfully, do not run any further middleware, call the app's not found handler.

## Not Found Handler

If no middleware completes the request the not found handler will be called:

```php
$app->notFoundHandler(function(Request $req, Response $res, App $app) {
	$res->setStatus(404)
		->text("Not Found");
});
```

## Exception Handler

Any uncaught exceptions thrown during execution of middleware will be refered to the app's error handler:

```php
$app->errorHandler(function(Request $req, Response $res, App $app, Exception $e) {
	$app->error($e);
	$res->setStatus(500)
		->text("Error: {$e->getMessage())}");
});
```