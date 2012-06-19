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
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * The PdfToText object.
 *
 * This binary adapter is used to extract text from PDF with the PdfToText
 * binary provided by XPDF.
 *
 * @see https://wikipedia.org/wiki/Pdftotext
 * @license MIT
 * @author Romain Neutron <imprec@gmail.com>
 */
class PdfToText
{
    protected $binary;
    protected $logger;
    protected $pathfile;
    protected $pageQuantity;
    protected $charset = 'UTF-8';

    /**
     * Constructor
     *
     * @param string $binary The path to the `pdftotext` binary
     * @param Logger $logger A logger wich will log the events
     */
    public function __construct($binary, Logger $logger)
    {
        $this->binary = $binary;
        $this->logger = $logger;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
        $this->logger->addDebug('Destructing PdfToText');
        $this->binary = $this->logger = null;
    }

    /**
     *
     * Set the default number of page to extract
     * When extracting text, if no page end is provided and this value has been
     * set, then the quantity will be limited.
     *
     * Set this value to null to reset it
     *
     * @param integer $page The numebr of page
     *
     * @return PdfToText
     * @throws Exception\InvalidArgumentException
     */
    public function setPageQuantity($pages)
    {
        if (null !== $pages && $pages < 1) {
            throw new InvalidArgumentException('The quantity must be greater or equal to 1');
        }

        $this->pageQuantity = (int) $pages;

        return $this;
    }

    /**
     * Opens a PDF file in order to extract text
     *
     * @param  string    $pathfile The path to the PDF file to extract
     * @return PdfToText
     *
     * @throws Exception\InvalidArgumentException
     */
    public function open($pathfile)
    {
        $this->logger->addInfo(sprintf('PdfToText opens %s', $pathfile));

        if ( ! file_exists($pathfile)) {
            $this->logger->addError(sprintf('PdfToText file %s does not exists', $pathfile));
            throw new Exception\InvalidArgumentException(sprintf('%s is not a valid file', $pathfile));
        }

        $this->pathfile = $pathfile;

        return $this;
    }

    /**
     * Close the current open file
     *
     * @return PdfToText
     */
    public function close()
    {
        $this->logger->addInfo(sprintf('PdfToText closes %s', $this->pathfile));
        $this->pathfile = null;

        return $this;
    }

    /**
     * Set the output encoding. If the charset is invalid, the getText method
     * will fail.
     *
     * @param  string    $charset The output charset
     * @return PdfToText
     */
    public function setOuputEncoding($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Get the ouput encoding, default is UTF-8
     *
     * @return string
     */
    public function getOuputEncoding()
    {
        return $this->charset;
    }

    /**
     * Extract the text of the current open PDF file, if not page start/end
     * provided, etxract all pages
     *
     * @param  integer $page_start The starting page number (first is 1)
     * @param  integer $page_end   The ending page number
     * @return string  The extracted text
     *
     * @throws Exception\LogicException
     * @throws Exception\RuntimeException
     */
    public function getText($page_start = null, $page_end = null)
    {
        if ( ! $this->pathfile) {
            $this->logger->addDebug('PdfToText no file open, unable to extract text');
            throw new Exception\LogicException('You must open a file to get some text');
        }

        $cmd = $this->binary;

        if ($page_start || $this->pageQuantity !== null) {
            $cmd .= ' -f ' . (int) $page_start;
        }

        if ($page_end) {
            $cmd .= ' -l ' . (int) $page_end;
        } elseif ($this->pageQuantity) {
            $cmd .= ' -l ' . ((int) $page_start + $this->pageQuantity);
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'xpdf');

        $cmd .= ' -raw -nopgbrk -enc ' . escapeshellarg($this->charset) . ' -eol unix '
            . ' ' . escapeshellarg($this->pathfile)
            . ' ' . escapeshellarg($tmpFile);

        $this->logger->addInfo(sprintf('PdfToText executing %s', $cmd));

        $process = new Process($cmd);
        $success = false;

        try {
            $process->run();
        } catch (\Symfony\Component\Process\Exception\RuntimeException $e) {

        }

        $ret = null;

        if ($process->isSuccessful()) {
            $success = true;
            $ret = file_get_contents($tmpFile);
            $this->logger->addDebug(sprintf('PdfToText command success, result is %d long', strlen($ret)));
        } else {
            $this->logger->addError(sprintf('Process failed : %s', $process->getErrorOutput()));
        }

        if (is_writable($tmpFile)) {
            unlink($tmpFile);
        }

        if ( ! $success) {
            $this->logger->addDebug(sprintf('PdfToText command failed', $cmd));
            throw new Exception\RuntimeException('Unable to extract text : ' . $process->getErrorOutput());
        }

        return $ret;
    }

    /**
     * Look for pdftotext binary and return a new XPDF object
     *
     * @param  Logger    $logger The logger
     * @return PdfToText
     *
     * @throws Exception\BinaryNotFoundException
     */
    public static function load(Logger $logger)
    {
        $finder = new ExecutableFinder();

        if (null !== $binary = $finder->find(static::getBinaryName())) {
            $logger->addInfo(sprintf('PdfToText loading with binary %s', $binary));

            return new static($binary, $logger);
        }

        $logger->addInfo('PdfToText not found');

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
