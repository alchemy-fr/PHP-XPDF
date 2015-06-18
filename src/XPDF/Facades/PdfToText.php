<?php 

namespace XPDF\Facades;

use Illuminate\Support\Facades\Facade;

class PdfToText extends Facade {

    protected static function getFacadeAccessor() { return 'pdftotext'; }

}