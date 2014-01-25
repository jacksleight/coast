# Examples

All of these examples use [Composer](https://getcomposer.org/doc/00-intro.md) to fetch and autoload the library, to get started:

```bash
cd examples/[example name]
composer.phar update
```

You can then run them through PHP's CLI web server:

```bash
php -S localhost:8000 app.php
```

Or alternatively through Apache using the included .htaccess files.

## Applications

* [**Hello World**](hello-world)  
	Basic hello world.
* [**Middleware**](middleware)  
	Example of how middleware return values and errors are handled.
* [**View**](view)  
	Usage of the `Js\View` component.

