# Coast

[![Latest Stable Version](https://poser.pugx.org/jacksleight/coast/v/stable.png)](https://packagist.org/packages/jacksleight/coast)
[![Latest Unstable Version](https://poser.pugx.org/jacksleight/coast/v/unstable.png)](https://packagist.org/packages/jacksleight/coast)
[![Build Status](https://travis-ci.org/jacksleight/coast.png?branch=master)](https://travis-ci.org/jacksleight/coast)
[![Coverage Status](https://coveralls.io/repos/jacksleight/coast/badge.png)](https://coveralls.io/r/jacksleight/coast)

Coast is a web application framework for PHP 5.5+. The goal of Coast is to provide a simple, lightweight and flexible framework for building high-performance, modern web apps. Some parts of the API were inspired by the Connect and Express node.js frameworks, others are my own (possibly ridiculous) ideas.

* Easy handling of **request and response data**
* View component for **view, partial and layout rendering**
* Router component for **advanced path routing**
* Controller component for **advanced request handling**
* URL component for **easy URL generation** (static files, routes, query strings etc.)
* Utility classes for working with **config files**, **URLs**, **DOM**, **Atom feeds**, **XML sitemaps** and the **file system**, plus a basic **HTTP client** library

All feedback, suggestions, bug reports and contributions are very welcome. Please feel free to get in touch by email (jacksleight at gmail dot com) or [@jacksleight](https://twitter.com/jacksleight) on Twitter.

**The documentation and tests are still a work in progress (sorry). Until there's a stable release the API may change.**

## Installation

The easiest way to install Coast is through [Composer](https://getcomposer.org/doc/00-intro.md), by creating a file called `composer.json` containing:

```json
{
    "require": {
        "jacksleight/coast": "0.1.*"
    }
}
```

And then running:

```bash
composer.phar update
```

## Hello World

Create a new file called `app.php` containing:

```php
<?php
use Coast\App,
    Coast\App\Request, 
    Coast\App\Response;

chdir(__DIR__);
require 'vendor/autoload.php';

$app = new App();
$app->add(function(Request $req, Response $res) {
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

1. Include the Composer autoloader.
2. Initialise a `Coast\App` object.
3. Add middleware to handle the request.
4. Call `execute` to run the application.

The `execute` method expects a `Coast\App\Request` object, and returns a `Coast\App\Response` object. The `import` method imports all of the request data from PHP's globals, and the `export` method exports the response data to the output buffer. It's also possible construct the request data manually, and inspect the response, which is useful for testing.

## Documentation & Examples

[Examples](examples)  
[API Documentation](http://rawgithub.com/jacksleight/coast/master/docs/index.html)  

## Requirements

* PHP 5.5+
* mod_rewrite (if using Apache)

## To Do

* Examples (in progress)
* API documentation (in progress)
* Tests (in progress)

## Roadmap

All of the items below are in progress, in fact they already exist as mostly working components of the library that became Coast, I've just not had a chance to update them yet.

* Internationalisation component
* Base entity class with validation
* Image component with automatic image resizing
* oEmbed component for including embedable content
* HTML tidy component

## Licence

The MIT License

Copyright (c) 2014 Jack Sleight <http://jacksleight.com/>

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
