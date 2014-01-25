# JS Framework

JS is a web application framework for PHP 5.5+. Parts of the API are inspired by the node.js Connect and Express frameworks, however it is not a direct copy/mirror. *This library and the documentation are sill a work in progress, the API may change, particularly the undocumented parts.*

## Barebones Hello World

Create a new directory, clone this repo into `vendor/jacksleight/js`, create a new file called `app.php` containing:

```php
<?php
use Js\App,
	Js\App\Request, 
	Js\App\Response;

require 'vendor/js/lib/Js.php';
set_include_path(get_include_path() . PATH_SEPARATOR . 'vendor/jacksleight/js/lib');
spl_autoload_register();

$app = new App();
$app->add(function(Request $req, Response $res, App $app) {
	return $res->text('Hello World');
});

$app->execute((new Request())->import())->export();
```
	
Then run:

```bash
php -S localhost:8000 app.php
```
	
And load it up in the browser at: [http://localhost:8000/](http://localhost:8000/).

### What's happening here?

1. Include files and configure an autoloader.
2. Initialise a `Js\App` object.
3. Add middlewear to handle the request.
4. Call `execute` to run the application.

The `execute` method expects a `Js\App\Request` object, and returns a `Js\App\Response` object. The `import` method grabs all of the request data from PHP's globals, and the `export` method sends the response data back out. It is also possible to skip these methods and construct the request data manually, which is useful for testing.

## Example Applications

[Browse Examples](examples)

## Requirements

* PHP 5.5+
* mod_rewrite (if using Apache)

## To Do

* User documentation
* API documentation
* Tests

## Licence

Copyright (c) 2014 Jack Sleight

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.