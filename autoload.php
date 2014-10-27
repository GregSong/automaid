<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/24
 * Time: 00:46
 */
// for phar
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    $loader = require_once(__DIR__.'/vendor/autoload.php');
    $loader->add('Codeception', __DIR__ . '/src');
    $loader->register(true);
} elseif (file_exists(__DIR__.'/../../autoload.php')) {
    $loader = (require_once __DIR__ . '/../../autoload.php');
}

return $loader;
