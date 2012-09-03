<?php

namespace XPDF;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Silex\Application;
use Silex\ServiceProviderInterface;

class XPDFServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['xpdf.pdf2text.binary'] = null;
        $app['xpdf.logger'] = null;

        $app['xpdf.pdf2text'] = $app->share(function(Application $app) {

            if ($app['xpdf.logger']) {
                $logger = $app['xpdf.logger'];
            } else {
                $logger = new Logger('xpdf');
                $logger->pushHandler(new NullHandler());
            }

            if ($app['xpdf.pdf2text.binary']) {
                return new PdfToText($app['xpdf.pdf2text.binary'], $logger);
            } else {
                return PdfToText::load($logger);
            }
        });
    }

    public function boot(Application $app)
    {
    }
}
