<?php

namespace XPDF;

use Silex\Application;
use Silex\ServiceProviderInterface;

class XPDFServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['xpdf.configuration'] = array();
        $app['xpdf.default.configuration'] = array(
            'pdftotext.timeout'  => 60,
            'pdftotext.binaries' => 'pdftotext',
        );
        $app['xpdf.logger'] = null;

        $app['xpdf.configuration.build'] = $app->share(function (Application $app) {
            return array_replace($app['xpdf.default.configuration'], $app['xpdf.configuration']);
        });

        $app['xpdf.pdftotext'] = $app->share(function(Application $app) {
            $configuration = $app['xpdf.configuration.build'];

            if (isset($configuration['pdftotext.timeout'])) {
                $configuration['timeout'] = $configuration['pdftotext.timeout'];
            }

            return PdfToText::create($configuration, $app['xpdf.logger']);
        });
    }

    public function boot(Application $app)
    {
    }
}
