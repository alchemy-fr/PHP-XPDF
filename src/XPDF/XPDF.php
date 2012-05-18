<?php

/*
 * This file is part of PHP-XPDF.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace XPDF;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * The XPDF object.
 */
class XPDF
{
    protected $binary;
    protected $logger;
    protected $pathfile;
    protected $charset = 'UTF-8';

    /**
     * Constructor
     *
     * @param string $binary The path to the `pdftotext` binary
     * @param Logger $logger The logger
     */
    public function __construct($binary, Logger $logger = null)
    {
        $this->binary = $binary;

        if ( ! $logger) {
            $logger = new Logger('xpdf');
            $logger->pushHandler(new NullHandler());
        }

        $this->logger = $logger;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
        $this->binary = $this->logger = null;
    }

    /**
     * Opens a PDF file to extract the text
     *
     * @param   string      $pathfile The path to the PDF file to extract
     * @return  \XPDF\XPDF
     *
     * @throws  Exception\InvalidFileArgumentException
     */
    public function open($pathfile)
    {
        if ( ! file_exists($pathfile)) {
            throw new Exception\InvalidFileArgumentException(sprintf('%s is not a valid file', $pathfile));
        }

        $this->pathfile = $pathfile;

        return $this;
    }

    /**
     * Close the current open file
     *
     * @return \XPDF\XPDF
     */
    public function close()
    {
        $this->pathfile = null;

        return $this;
    }

    /**
     * Set the output encoding. If the charset is invalid, the getText method
     * will fail.
     *
     * @param   string      $charset The charset
     * @return  \XPDF\XPDF
     */
    public function setOuputEncoding($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Get the ouput encoding, default is UTF-8
     *
     * @return type
     */
    public function getOuputEncoding()
    {
        return $this->charset;
    }

    /**
     * Extract the text of the current open PDF file, if not page start/end
     * provided, etxract all pages
     *
     * @param   int     $page_start     The starting page number (first is 1)
     * @param   int     $page_end       The ending page number
     * @return  string                  The extracted text
     *
     * @throws  Exception\LogicException
     * @throws  Exception\RuntimeException
     */
    public function getText($page_start = null, $page_end = null)
    {
        if ( ! $this->pathfile) {
            throw new Exception\LogicException('You must open a file to get some text');
        }

        $cmd = $this->binary;

        if ($page_start) {
            $cmd .= ' -f ' . (int) $page_start;
        }
        if ($page_end) {
            $cmd .= ' -l ' . (int) $page_end;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'xpdf');

        $cmd .= ' -raw -nopgbrk -enc ' . $this->charset . ' -eol unix '
            . ' ' . escapeshellarg($this->pathfile)
            . ' ' . escapeshellarg($tmpFile);

        $this->logger->addInfo(sprintf('Processing %s', $cmd));

        $process = new Process($cmd);
        $success = false;

        try {
            $process->run();
        } catch (Exception\Exception $e) {

        }

        $ret = null;

        if ($process->isSuccessful()) {
            $success = true;
            $ret = file_get_contents($tmpFile);
        } else {
            $this->logger->addError(sprintf('Process failed : %s', $process->getErrorOutput()));
        }

        if (is_writable($tmpFile)) {
            unlink($tmpFile);
        }

        if ( ! $success) {
            throw new Exception\RuntimeException('Unable to extract text : ' . $process->getErrorOutput());
        }

        return $ret;
    }

    /**
     * Look for pdftotext binary and return a new XPDF object
     *
     * @param   \Monolog\Logger $logger The logger
     * @return  \XPDF\XPDF
     *
     * @throws Exception\BinaryNotFoundException
     */
    public static function load(\Monolog\Logger $logger = null)
    {
        $finder = new ExecutableFinder();

        if (null !== $binary = $finder->find(static::getBinaryName())) {
            return new static($binary, $logger);
        }

        throw new Exception\BinaryNotFoundException('Binary not found');
    }

    /**
     * Return the binary name
     *
     * @return string
     */
    protected static function getBinaryName()
    {
        return 'pdftotext';
    }
}
