# Response

## HTML

```php		
$res->html('<p>Hello World</p>');
```

## JSON

```php
$res->json(['Hello World']);
```

## XML

```php
$dom = new \DOMDocument();
$dom->appendChild($dom->createElement('hello-world'));
$res->xml($dom);
```

## Other

```php
$res->setHeader('Content-Type', 'application/pdf')->setBody($data);
```