<?php

namespace XPDF\Tests;

use XPDF\XPDFServiceProvider;
use Silex\Application;
use Symfony\Component\Process\ExecutableFinder;

class XPDFServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $finder = new ExecutableFinder();
        $php = $finder->find('php');

        if (null === $php) {
            $this->markTestSkipped('Unable to find PHP binary, required for this test');
        }

        $logger = $this->getMock('Psr\Log\LoggerInterface');

        $app = new Application();
        $app->register(new XPDFServiceProvider(), array(
            'xpdf.configuration' => array(
                'pdftotext.timeout'  => 42,
                'pdftotext.binaries' => $php,
            ),
            'xpdf.logger' => $logger,
        ));
        $app->boot();

        $this->assertInstanceOf('XPDF\PdfToText', $app['xpdf.pdftotext']);
        $this->assertEquals(42, $app['xpdf.pdftotext']->getProcessBuilderFactory()->getTimeout());
        $this->assertEquals($logger, $app['xpdf.pdftotext']->getProcessRunner()->getLogger());
        $this->assertEquals($php, $app['xpdf.pdftotext']->getProcessBuilderFactory()->getBinary());
    }
}
