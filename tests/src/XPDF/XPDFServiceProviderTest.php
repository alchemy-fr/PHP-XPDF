<?php

namespace XPDF;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Silex\Application;

class PdfToTextTest extends \PHPUnit_Framework_TestCase
{
    private function getApplication()
    {
        return new Application;
    }

    public function testInit()
    {
        $app = $this->getApplication();
        $app->register(new XPDFServiceProvider());

        $this->assertInstanceOf('\\XPDF\\PdfToText', $app['xpdf.pdf2text']);
    }
}
