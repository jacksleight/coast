# Servers

## PHP CLI

```bash
php -S localhost:8000 app.php
```

## Apache

```apache
RewriteEngine On
RewriteRule (.*) app.php?_=$1 [QSA,L]
```