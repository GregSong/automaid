<?php
// This is global bootstrap for autoloading

use Doctrine\Common\Annotations\AnnotationRegistry;

date_default_timezone_set('Asia/Shanghai');

$loader = (require __DIR__ . '/../vendor/autoload.php');
$loader->add('Greg\\ATC\\TestBundle\\', __DIR__ . '/_data/project/src');

AnnotationRegistry::registerLoader(
    array(
        $loader,
        'loadClass'
    )
);

require_once __DIR__ . '/_code/DITestMagicService.php';
require_once __DIR__ . '/_code/DITestServiceOneTraits.php';
require_once __DIR__ . '/_code/DITestServiceOne.php';
require_once __DIR__ . '/_code/DITestServiceTwoTraits.php';
require_once __DIR__ . '/_code/DITestServiceTwo.php';
require_once __DIR__ . '/_code/DITestBase.php';


