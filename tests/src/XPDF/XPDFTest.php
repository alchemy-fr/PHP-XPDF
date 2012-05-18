<?php

namespace XPDF;

class XPDFTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var XPDF
     */
    protected $object;

    /**
     * @covers XPDF\XPDF::__construct
     */
    protected function setUp()
    {
        $this->object = new XPDF('pdftotext');
    }

    /**
     * @covers XPDF\XPDF::__construct
     */
    public function testConstruct()
    {
        new XPDF('pdftotext', new \Monolog\Logger('xpdf'));
    }

    /**
     * @covers XPDF\XPDF::__destruct
     */
    protected function tearDown()
    {
        $this->object = null;
    }

    /**
     * @covers XPDF\XPDF::open
     */
    public function testOpen()
    {
        $this->object->open(__DIR__ . '/../../files/HelloWorld.pdf');
    }

    /**
     * @covers XPDF\XPDF::open
     * @expectedException XPDF\Exception\InvalidFileArgumentException
     */
    public function testOpenWrongFile()
    {
        $this->object->open('unexistantfile.pdf');
    }

    /**
     * @covers XPDF\XPDF::close
     */
    public function testCloseNoFile()
    {
        $this->object->close();
    }

    /**
     * @covers XPDF\XPDF::close
     */
    public function testCloseFile()
    {
        $this->object->open(__DIR__ . '/../../files/HelloWorld.pdf');
        $this->object->close();
    }

    /**
     * @covers XPDF\XPDF::close
     * @expectedException \XPDF\Exception\LogicException
     */
    public function testProcessClose()
    {
        $this->object->open(__DIR__ . '/../../files/HelloWorld.pdf');
        $this->object->close();
        $this->object->getText();
    }

    /**
     * @covers XPDF\XPDF::close
     * @expectedException \XPDF\Exception\LogicException
     */
    public function testProcessNotOpen()
    {
        $this->object->getText();
    }

    /**
     * @covers XPDF\XPDF::setOuputEncoding
     * @covers XPDF\XPDF::getOuputEncoding
     */
    public function testSetOuputEncoding()
    {
        $this->assertEquals('UTF-8', $this->object->getOuputEncoding());
        $this->object->setOuputEncoding('ascii');
        $this->assertEquals('ascii', $this->object->getOuputEncoding());
    }

    /**
     * @covers XPDF\XPDF::getText
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
     * @covers XPDF\XPDF::getText
     * @expectedException XPDF\Exception\RuntimeException
     */
    public function testGetTextFail()
    {
        $this->object->open(__DIR__ . '/../../files/HelloWorld.pdf');
        $this->object->setOuputEncoding('us-ascii');
        $this->object->getText();
    }

    /**
     * @covers XPDF\XPDF::getText
     * @expectedException XPDF\Exception\LogicException
     */
    public function testGetTextNoFile()
    {
        $this->object->getText();
    }

    /**
     * @covers XPDF\XPDF::load
     * @covers XPDF\XPDF::getBinaryName
     */
    public function testLoad()
    {
        $this->assertInstanceOf('\\XPDF\\XPDF', \XPDF\XPDF::load());
        $this->assertInstanceOf('\\XPDF\\XPDF', \XPDF\XPDF::load(new \Monolog\Logger('xpdf')));
    }

    /**
     * @covers XPDF\XPDF::load
     * @covers XPDF\XPDF::getBinaryName
     * @expectedException XPDF\Exception\BinaryNotFoundException
     */
    public function testGetBinaryName()
    {
        $this->assertInstanceOf('\\XPDF\\XPDF', \XPDF\XPDFtester::load());
    }
}

class XPDFtester extends XPDF
{

    protected static function getBinaryName()
    {
        return 'dudule56786';
    }
}
