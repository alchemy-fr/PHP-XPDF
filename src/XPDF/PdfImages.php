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

use Alchemy\BinaryDriver\AbstractBinary;
use Alchemy\BinaryDriver\Exception\ExecutionFailureException;
use Alchemy\BinaryDriver\Exception\ExecutableNotFoundException;
use Alchemy\BinaryDriver\Configuration;
use Alchemy\BinaryDriver\ConfigurationInterface;
use Psr\Log\LoggerInterface;
use XPDF\Exception\InvalidArgumentException;
use XPDF\Exception\RuntimeException;
use XPDF\Exception\BinaryNotFoundException;

/**
 * The PdfImages object.
 *
 * This binary adapter is used to extract images from PDF with the PdfImages
 * binary provided by XPDF.
 *
 * @see https://wikipedia.org/wiki/Pdfimages
 * @license MIT
 */
class PdfImages extends AbstractBinary
{
    private $pages;
    private $output_format = '';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdfimages';
    }

    /**
     * Sets a quantity of page to extract by default
     *
     * @param integer $pages
     *
     * @return PdfImages
     */
    public function setPageQuantity($pages)
    {
        if (0 >= $pages) {
            throw new InvalidArgumentException('Page quantity must be a positive value');
        }

        $this->pages = $pages;

        return $this;
    }

    /**
     * Set the image output format.
     *
     * Normally, all images are written as PBM (for monochrome  images)
     * or  PPM  (for  non-monochrome  images) files.  This option allows
     * images in DCT format to be saved  as  JPEG  files.   All  non-DCT
     * images are saved in PBM/PPM format as usual regardless of format.
     *  
     * @param string $format
     * 
     * @return PdfImages
     *
     * Valid formats are "jpeg" and "bitmap"
     */
    public function setOutputFormat($format)
    {
        switch (strtolower($format)) {
            case 'jpeg':
            case 'jpg':
            case '-j':
                $this->output_format = '-j';
                break;
            case 'bitmap':
                $this->output_format = '';
                break;
            default:
                throw new InvalidArgumentException('Format must be "jpeg" or "bitmap"');
                break;
        }

        return $this;
    }

    /**
     * Extracts images from the current open PDF file, if not page start/end
     * provided, extract from all pages.
     *
     * Image files will end in .ppm OR .pbm by default, and placed in the 
     * system temp directory.
     * 
     * To save images as .jpg (where possible), use:
     *     $pdfImages->setOutputFormat('jpeg');
     *
     * @param string  $pathfile   The path to the PDF file
     * @param integer $page_start The starting page number (first is 1)
     * @param integer $page_end   The ending page number
     *
     * @return array File paths of the extracted images
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function getImages($pathfile, $page_start = null, $page_end = null)
    {
        if ( ! file_exists($pathfile)) {
            throw new InvalidArgumentException(sprintf('%s is not a valid file', $pathfile));
        }

        $commands = array();

        if (null !== $page_start) {
            $commands[] = '-f';
            $commands[] = (int) $page_start;
        }
        if (null !== $page_end) {
            $commands[] = '-l';
            $commands[] = (int) $page_end;
        } elseif (null !== $this->pages) {
            $commands[] = '-l';
            $commands[] = (int) $page_start + $this->pages;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'xpdf');

        if ($this->output_format) {
            $commands[] = $this->output_format;
        }
        $commands[] = $pathfile;
        $commands[] = $tmpFile;

        try {
            $this->command($commands);
            
            if (is_writable($tmpFile)) {
                unlink($tmpFile);
            }

            $ret = glob($tmpFile . '*');

        } catch (ExecutionFailureException $e) {
            throw new RuntimeException('Unable to extract images', $e->getCode(), $e);
        }

        return $ret;
    }

    /**
     * Factory for PdfImages
     *
     * @param array|Configuration $configuration
     * @param LoggerInterface     $logger
     *
     * @return PdfImages
     */
    public static function create($configuration = array(), LoggerInterface $logger = null)
    {
        if (!$configuration instanceof ConfigurationInterface) {
            $configuration = new Configuration($configuration);
        }

        $binaries = $configuration->get('pdfimages.binaries', 'pdfimages');

        if (!$configuration->has('timeout')) {
            $configuration->set('timeout', 60);
        }

        try {
            return static::load($binaries, $logger, $configuration);
        } catch (ExecutableNotFoundException $e) {
            throw new BinaryNotFoundException('Unable to find pdfimages', $e->getCode(), $e);
        }
    }
}
