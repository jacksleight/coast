# Servers

## PHP

###### .htaccess

```bash
php -S localhost:8000 app.php
```

## Apache

###### .htaccess

```apache
RewriteEngine On
RewriteRule (.*) app.php?_=$1 [QSA,L]
```