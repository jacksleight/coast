# URL

## Configration Options

* **base** (/)  
    The base URL/path to prefix to all URLs.
* **dir**  
    The base directory files are served from (if the application root dir is not equal to the web server document root).
* **cdnBase**  
    An alternative base URL/path to serve files from.
* **version** (false)  
    Whether to timestamp file paths with the modify time (currently works in Apache with `RewriteRule ^(.*).[0-9]{10}(\.\w+)$ $1$2 [L]`).
* **router**  
    The router component to use for route based URLs.