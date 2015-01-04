# Coast

[![Packagist](http://img.shields.io/packagist/v/jacksleight/coast.svg?style=flat-square)](https://packagist.org/packages/jacksleight/coast)
[![Travis](http://img.shields.io/travis/jacksleight/coast/dev.svg?style=flat-square)](https://travis-ci.org/jacksleight/coast)
[![Coverage Status](http://img.shields.io/coveralls/jacksleight/coast/dev.svg?style=flat-square)](https://coveralls.io/r/jacksleight/coast)
[![License](http://img.shields.io/packagist/l/jacksleight/coast.svg?style=flat-square)](https://packagist.org/packages/jacksleight/coast)

Coast is a web application framework for PHP 5.5+. The goal of Coast is to provide a simple, lightweight and flexible framework for building high-performance, modern web apps. Some parts of the API were inspired by the Connect and Express node.js frameworks, others are my own (possibly ridiculous) ideas.

* Easy handling of **request and response data**
* View component for **view, partial and layout rendering**
* Router component for **advanced path routing**
* Controller component for **advanced request handling**
* URL component for **easy URL generation** (static files, routes, query strings etc.)
* Utility classes for working with **config files**, **URLs**, **Atom feeds**, **XML sitemaps** and the **file system**, plus a basic **HTTP client** library

All feedback, suggestions, bug reports and contributions are very welcome. Please feel free to get in touch by email (jacksleight at gmail dot com) or [@jacksleight](https://twitter.com/jacksleight) on Twitter.

**The documentation and tests are still a work in progress (sorry). Until there's a stable release the API may change.**

## Installation

The easiest way to install Coast is through [Composer](https://getcomposer.org/doc/00-intro.md), by creating a file called `composer.json` containing:

```json
{
    "require": {
        "jacksleight/coast": "dev-master"
    }
}
```

And then running:

```bash
composer.phar install
```

## Hello World

Create a new file called `app.php` containing:

```php
<?php
use Coast\App,
    Coast\Request, 
    Coast\Response;

require __DIR__ . '/vendor/autoload.php';

$app = new App(__DIR__);
$app->executable(function(Request $req, Response $res) {
    return $res->text('Hello World');
});

$app->execute();
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

## Documentation

* [API Documentation](https://rawgit.com/jacksleight/coast/docs/index.html)  
* [Examples](examples) (see the [test suite](tests/Coast) for further code exammples)

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
