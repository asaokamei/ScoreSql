<?php

use Composer\Autoload\ClassLoader;
use WScore\ScoreSql\Builder\Bind;

if (defined('VENDOR_DIRECTORY')) {
    return;
} elseif (file_exists(__DIR__ . '/../vendor/')) {
    define('VENDOR_DIRECTORY', __DIR__ . '/../vendor/');
} elseif (file_exists(__DIR__ . '/../../../../vendor/')) {
    define('VENDOR_DIRECTORY', __DIR__ . '/../../../../vendor/');
} else {
    die('vendor directory not found');
}
require_once(VENDOR_DIRECTORY . 'autoload.php');
$loader = new ClassLoader();

$loader->add('WSTest', __DIR__);
$loader->register();

Bind::$useColumnInBindValues = true;

