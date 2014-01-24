# Loaders

JS does not perform any class loading and does not include an autoloader. You can use any class loader you like (or roll your own), but I highly reccomend Composer's.

### Composer

**composer.json**

```json
{
	"autoload": {
		"psr-0": {
			"Js": [
				"vendor/js/"
			]
		},
		"files": [
			"vendor/js/lib/Js.php"
		]
	}
}
```

**app.php**

```php
require 'vendor/autoload.php';
```

### SPL

**app.php**

```php
require 'vendor/js/lib/Js.php';
set_include_path(get_include_path() . PATH_SEPARATOR . 'vendor/js/lib');
spl_autoload_register();
```

## File and Directory Paths

If you're using relative paths during configuration you should always ensure that the current working directory is correct by calling `chdir` at the top of app.php:

**app.php**

```php
chdir(__DIR__);
```