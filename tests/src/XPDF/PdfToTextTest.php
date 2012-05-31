<?php

namespace XPDF;

class PdfToTextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PdfToText
     */
    protected $object;

    /**
     * @covers XPDF\PdfToText::__construct
     */
    protected function setUp()
    {
        $this->logger = new \Monolog\Logger('PHPUnit');
        $this->logger->pushHandler(new \Monolog\Handler\NullHandler());

        $this->object = new PdfToText('pdftotext', $this->logger);
    }

    /**
     * @covers XPDF\PdfToText::__construct
     */
    public function testConstruct()
    {
        new PdfToText('pdftotext', $this->logger);
    }

    /**
     * @covers XPDF\PdfToText::__destruct
     */
    protected function tearDown()
    {
        $this->object = null;
    }

    /**
     * @covers XPDF\PdfToText::open
     */
    public function testOpen()
    {
        $this->object->open(__DIR__ . '/../../files/HelloWorld.pdf');
    }

    /**
     * @covers XPDF\PdfToText::open
     * @expectedException XPDF\Exception\InvalidFileArgumentException
     */
    public function testOpenWrongFile()
    {
        $this->object->open('unexistantfile.pdf');
    }

    /**
     * @covers XPDF\PdfToText::close
     */
    public function testCloseNoFile()
    {
        $this->object->close();
    }

    /**
     * @covers XPDF\PdfToText::close
     */
    public function testCloseFile()
    {
        $this->object->open(__DIR__ . '/../../files/HelloWorld.pdf');
        $this->object->close();
    }

    /**
     * @covers XPDF\PdfToText::close
     * @expectedException \XPDF\Exception\LogicException
     */
    public function testProcessClose()
    {
        $this->object->open(__DIR__ . '/../../files/HelloWorld.pdf');
        $this->object->close();
        $this->object->getText();
    }

    /**
     * @covers XPDF\PdfToText::close
     * @expectedException \XPDF\Exception\LogicException
     */
    public function testProcessNotOpen()
    {
        $this->object->getText();
    }

    /**
     * @covers XPDF\PdfToText::setOuputEncoding
     * @covers XPDF\PdfToText::getOuputEncoding
     */
    public function testSetOuputEncoding()
    {
        $this->assertEquals('UTF-8', $this->object->getOuputEncoding());
        $this->object->setOuputEncoding('ascii');
        $this->assertEquals('ascii', $this->object->getOuputEncoding());
    }

    /**
     * @covers XPDF\PdfToText::getText
     */
    public function testGetText()
    {
        $text = 'This is an UTF-8 encoded string : « Un éléphant ça trompe énormément ! »
It tells about elephant\'s noze !
';
        $this->object->open(__DIR__ . '/../../files/HelloWorld.pdf');
        $this->assertEquals($text, $this->object->getText());
        $this->assertEquals($text, $this->object->getText(1, 1));
        $this->assertEquals('', $this->object->getText(2, 2));
    }

    /**
     * @covers XPDF\PdfToText::getText
     * @expectedException XPDF\Exception\RuntimeException
     */
    public function testGetTextFail()
    {
        $this->object->open(__DIR__ . '/../../files/HelloWorld.pdf');
        $this->object->setOuputEncoding('ISO-8860');
        $this->object->getText();
    }

    /**
     * @covers XPDF\PdfToText::getText
     * @expectedException XPDF\Exception\LogicException
     */
    public function testGetTextNoFile()
    {
        $this->object->getText();
    }

    /**
     * @covers XPDF\PdfToText::load
     * @covers XPDF\PdfToText::getBinaryName
     */
    public function testLoad()
    {
        $this->assertInstanceOf('\\XPDF\\PdfToText', \XPDF\PdfToText::load($this->logger));
    }

    /**
     * @covers XPDF\PdfToText::load
     * @covers XPDF\PdfToText::getBinaryName
     * @expectedException XPDF\Exception\BinaryNotFoundException
     */
    public function testGetBinaryName()
    {
        $this->assertInstanceOf('\\XPDF\\PdfToText', \XPDF\PdfToTexttester::load($this->logger));
    }
}

class PdfToTexttester extends PdfToText
{

    protected static function getBinaryName()
    {
        return 'dudule56786';
    }
}
