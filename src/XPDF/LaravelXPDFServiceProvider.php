<?php

namespace XPDF;

use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class LaravelXPDFServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishes([
            __DIR__.'/Config/config.php' => config_path('xpdf.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/Config/config.php', 'xpdf'
        );

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bindShared('pdftotext', function ($app) {

            $configuration = config('xpdf');

            return PdfToText::create($configuration, $app->make('Psr\Log\LoggerInterface'));

        });

        $this->app->bind('XPDF\PdfToText', function($app) {

            return $app['pdftotext'];
            
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('pdftotext');
    }

}
