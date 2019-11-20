# htaccess-api-client

This is an API client to interact with the [Htaccess tester](https://htaccess.madewithlove.be/).

### Installation

```bash
composer require madewithlove/htaccess-api-client
```

### Usage

The package can be used with every PSR-compatible http client. In this example, we're going to be using
guzzle's PSR adapter.

```php
use Http\Factory\Guzzle\ServerRequestFactory;
use Http\Adapter\Guzzle6\Client;
use Madewithlove\HtaccessClient

$client = new HtaccessClient(
    new Client(),
    new ServerRequestFactory()
);

$response = $client->test(
    'http://localhost',
    'RewriteRule .* /foo [R]'
);

$response->getOutputUrl(); // "http://localhost/foo"
$response->getLines();
/*
array(1) {
  [0]=>
  object(Madewithlove\ResultLine)#30 (5) {
    ["line":"Madewithlove\ResultLine":private]=> string(23) "RewriteRule .* /foo [R]"
    ["message":"Madewithlove\ResultLine":private]=> string(98) "The new url is http://localhost/foo
Test are stopped, a redirect will be made with status code 302"
    ["isMet":"Madewithlove\ResultLine":private]=> bool(true)
    ["isValid":"Madewithlove\ResultLine":private]=> bool(true)
    ["wasReached":"Madewithlove\ResultLine":private]=> bool(true)
  }
}
*/
```
