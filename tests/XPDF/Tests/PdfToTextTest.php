<?php

namespace XPDF\Tests;

use XPDF\PdfToText;
use Symfony\Component\Process\ExecutableFinder;

class PdfToTextTest extends \PHPUnit_Framework_TestCase
{
    public function testSetOuputEncoding()
    {
        $pdfToText = PdfToText::create();

        $this->assertEquals('UTF-8', $pdfToText->getOuputEncoding());
        $pdfToText->setOuputEncoding('ascii');
        $this->assertEquals('ascii', $pdfToText->getOuputEncoding());
    }

    public function testGetText()
    {
        $text = 'This is an UTF-8 encoded string : « Un éléphant ça trompe énormément ! »
It tells about elephant\'s noze !
';
        $pdfToText = PdfToText::create();
        $this->assertEquals($text, $pdfToText->getText(__DIR__ . '/../../files/HelloWorld.pdf'));
        $this->assertEquals($text, $pdfToText->getText(__DIR__ . '/../../files/HelloWorld.pdf', 1, 1));
        $this->assertEquals('', $pdfToText->getText(__DIR__ . '/../../files/HelloWorld.pdf', 2, 2));
    }

    public function testCreate()
    {
        $finder = new ExecutableFinder();
        $php = $finder->find('php');

        if (null === $php) {
            $this->markTestSkipped('Unable to find PHP binary, required for this test');
        }

        $logger = $this->getMock('Psr\Log\LoggerInterface');

        $pdfToText = PdfToText::create(array('pdftotext.binaries' => $php, 'timeout' => 42), $logger);
        $this->assertInstanceOf('XPDF\PdfToText', $pdfToText);
        $this->assertEquals(42, $pdfToText->getProcessBuilderFactory()->getTimeout());
        $this->assertEquals($logger, $pdfToText->getProcessRunner()->getLogger());
        $this->assertEquals($php, $pdfToText->getProcessBuilderFactory()->getBinary());
    }
}
