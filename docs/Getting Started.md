# Geting Started

Create a new directory, clone the rep into `vendor/js`, and create a new file called `app.php`.

### App Initilisation

```php
$app = new App();
```

### Middleware

```php
$app->add(function(Request $req, Response $res, App $app) {
	return $res->text('Hello World');rem
});
```

### Execution