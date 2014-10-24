<?php
// This is global bootstrap for autoloading
use Doctrine\Common\Annotations\AnnotationRegistry;
require_once __DIR__ . '/_code/DITestServiceOneTraits.php';
require_once __DIR__ . '/_code/DITestServiceTwoTraits.php';
require_once __DIR__ . '/_code/DITestServiceOne.php';
require_once __DIR__ . '/_code/DITestServiceTwo.php';
require_once __DIR__ . '/_code/DITestBase.php';
require_once __DIR__ . '/_code/DITestMagicService.php';


require_once __DIR__ . '/../app/AppKernel.php';

$loader = (require __DIR__ . '/../vendor/autoload.php');
AnnotationRegistry::registerLoader(
    array(
        $loader,
        'loadClass'
    )
);
