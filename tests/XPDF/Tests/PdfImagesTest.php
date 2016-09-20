<?php

namespace XPDF\Tests;

use XPDF\PdfImages;
use Symfony\Component\Process\ExecutableFinder;

class PdfImagesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException XPDF\Exception\BinaryNotFoundException
     */
    public function testBinaryNotFound()
    {
        PdfImages::create(array('pdfimages.binaries' => '/path/to/nowhere'));
    }

    /**
     * @expectedException XPDF\Exception\InvalidArgumentException
     */
    public function testGetImagesInvalidFile()
    {
        $pdfImages = PdfImages::create();
        $pdfImages->getImages('/path/to/nowhere');
    }

    public function testGetImages()
    {
        $pdfImages = PdfImages::create();
        
        $result = $pdfImages->getImages(__DIR__ . '/../../files/SearchResults.pdf', 1, 1);
        
        // ensure we have 40 images on first page, all ppm format
        $this->assertEquals(40, count($result));
        foreach ($result as $file) {
            $this->assertEquals(true, file_exists($file));
            $this->assertEquals(true, preg_match('/\.ppm$/', $file));
        }

        $this->removeTempFiles($result);
    }

    public function testGetImagesOutputFormat()
    {
        $pdfImages = PdfImages::create();
        $pdfImages->setOutputFormat('jpeg');

        $result = $pdfImages->getImages(__DIR__ . '/../../files/SearchResults.pdf', 1, 1);
        
        // ensure we have 40 images on first page, in jpg or ppm format
        $this->assertEquals(40, count($result));
        foreach ($result as $file) {
            $this->assertEquals(true, file_exists($file));
            $this->assertEquals(true, preg_match('/\.(ppm|jpg)$/', $file));
        }

        // count number of jpegs
        $jpegs = array_filter($result, function($file) {
            return preg_match('/\.jpg$/', $file);
        });

        // ensure we have 27 jpegs
        $this->assertEquals(27, count($jpegs));

        $this->removeTempFiles($result);
    }

    public function testGetImagesWithStartPage()
    {
        $pdfImages = PdfImages::create();
        
        $result = $pdfImages->getImages(__DIR__ . '/../../files/SearchResults.pdf', 2);

        // only 34 images on page two
        $this->assertEquals(34, count($result));
        foreach ($result as $file) {
            $this->assertEquals(true, file_exists($file));
            $this->assertEquals(true, preg_match('/\.(ppm)$/', $file));
        }

        $this->removeTempFiles($result);
    }


//     public function testGetText()
//     {
//         $text = 'This is an UTF-8 encoded string : « Un éléphant ça trompe énormément ! »
// It tells about elephant\'s noze !
// ';
//         $pdfToText = PdfToText::create();
//         $this->assertEquals($text, $pdfToText->getText(__DIR__ . '/../../files/HelloWorld.pdf'));
//         $this->assertEquals($text, $pdfToText->getText(__DIR__ . '/../../files/HelloWorld.pdf', 1, 1));
//         $this->assertEquals('', $pdfToText->getText(__DIR__ . '/../../files/HelloWorld.pdf', 2, 2));
//     }

//     public function testGetTextWithPageQuantity()
//     {
//         $text = 'This is an UTF-8 encoded string : « Un éléphant ça trompe énormément ! »
// It tells about elephant\'s noze !
// ';
//         $pdfToText = PdfToText::create();
//         $pdfToText->setPageQuantity(1);
//         $this->assertEquals($text, $pdfToText->getText(__DIR__ . '/../../files/HelloWorld.pdf'));
//     }

    /**
     * @expectedException XPDF\Exception\InvalidArgumentException
     */
    public function testInvalidPageQuantity()
    {
        $pdfImages = PdfImages::create();
        $pdfImages->setPageQuantity(0);
    }

    public function testCreate()
    {
        $finder = new ExecutableFinder();
        $php = $finder->find('php');

        if (null === $php) {
            $this->markTestSkipped('Unable to find PHP binary, required for this test');
        }

        $logger = $this->getMock('Psr\Log\LoggerInterface');

        $pdfImages = PdfImages::create(array('pdfimages.binaries' => $php, 'timeout' => 42), $logger);
        $this->assertInstanceOf('XPDF\PdfImages', $pdfImages);
        $this->assertEquals(42, $pdfImages->getProcessBuilderFactory()->getTimeout());
        $this->assertEquals($logger, $pdfImages->getProcessRunner()->getLogger());
        $this->assertEquals($php, $pdfImages->getProcessBuilderFactory()->getBinary());
    }

    protected function removeTempFiles($files) {
        foreach ($files as $file) {
            if (is_writable($file)) {
                unlink($file);
            }
        }
    }
}
