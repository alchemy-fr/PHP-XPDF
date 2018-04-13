<?php

include 'vendor/autoload.php';

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$dir = 'src';

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir)
;

$versions = GitVersionCollection::create($dir)
    ->add('SILEX2', 'SILEX2 branch')
    ->add('master', 'master branch')
;

return new Sami($iterator, array(
    'title'                => 'PHP-XPDF API',
    'versions'             => $versions,
    //'theme'                => 'enhanced',
    'build_dir'            => __DIR__.'/docs/source/API/API/%version%',
    'cache_dir'            => __DIR__.'/docs/source/API/API/%version%/cache',
    'default_opened_level' => 2,
));
