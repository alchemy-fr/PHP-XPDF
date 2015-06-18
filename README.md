# PHP-XPDF

[![Build Status](https://secure.travis-ci.org/alchemy-fr/PHP-XPDF.png?branch=master)](http://travis-ci.org/alchemy-fr/PHP-XPDF)

PHP-XPDF is an object oriented wrapper for XPDF. For the moment, only PdfTotext
wrapper is available.

## Installation

It is recommended to install PHP-XPDF through [Composer](http://getcomposer.org) :

```json
{
    "require": {
        "php-xpdf/php-xpdf": "~0.2.0"
    }
}
```

## Dependencies :

In order to use PHP-XPDF, you need to install XPDF. Depending of your
configuration, please follow the instructions at on the
[XPDF website](http://www.foolabs.com/xpdf/download.html).


## Documentation

### Driver Initialization

The easiest way to instantiate the driver is to call the `create method.

```php
$pdfToText = XPDF\PdfToText::create();
```

You can optionaly pass a configuration and a logger (any
`Psr\Logger\LoggerInterface`).

```php
$pdfToText = XPDF\PdfToText::create(array(
    'pdftotext.binaries' => '/opt/local/xpdf/bin/pdftotext',
    'pdftotext.timeout' => 30, // timeout for the underlying process
), $logger);
```

### Extract text

To extract text from PDF, use the `getText` method.

```php
$text = $pdtTotext->getText('document.pdf');
```

You can optionally extract from a page to another page.

```php
$text = $pdtTotext->getText('document.pdf', $from = 1, $to = 4);
```

You can also predefined how much pages would be extracted on any call.

```php
$pdtTotext->setpageQuantity(2);
$pdtTotext->getText('document.pdf'); // extracts page 1 and 2
```

### Use with Silex

A [Silex](http://silex.sensiolabs.org) service provider is available

```php
$app = new Silex\Application();
$app->register(new XPDF\XPDFServiceProvider());

$app['xpdf.pdftotext']->getText('document.pdf');
```

Options can be passed to customize the provider.

```php
$app->register(new XPDF\XPDFServiceProvider(), array(
    'xpdf.configuration' => array(
        'pdftotext.timeout'  => 30,
        'pdftotext.binaries' => '/opt/local/xpdf/bin/pdftotext',
    ),
    'xpdf.logger' => $logger,
));
```

### Use with Laravel 5

A [Laravel 5](http://laravel.com/) service provider is available

Publish the package config files:

    php artisan vendor:publish

You will also need to add the service provider and optionally the facade alias to your `app/config/app.php`:

```php
'providers' => array(
  'XPDF\LaravelXPDFServiceProvider'
)

'aliases' => array(
  'PdfToText' => 'XPDF\Facades\PdfToText'
),
```

## License

This project is licensed under the [MIT license](http://opensource.org/licenses/MIT).
