#PHP-XPDF

[![Build Status](https://secure.travis-ci.org/alchemy-fr/PHP-XPDF.png?branch=master)](http://travis-ci.org/alchemy-fr/PHP-XPDF)

PHP-XPDF is an object oriented wrapper for XPDF. For the moment, only PdfTotext
wrapper is available.

##PdfToText

This is an example how to extract text from a PDF

```php
use Monolog\Logger;
use Monolog\Handler\NullHandler;

$logger = new Logger('PHPUnit');
$logger->pushHandler(new NullHandler());

// You have to pass a Monolog logger
// This logger provides some usefull infos about what's happening
$pdfToText = PdfToText::load($logger);

$pdfToText->setOuputEncoding('UTF-8');
// Extract page 1 to 3
$text = $pdfToText->getText(1, 3));
$pdfToText->close();
```

See Monolog documentation for more informations about Monolog service
https://github.com/Seldaek/monolog

##License

This project is released under the MIT license
