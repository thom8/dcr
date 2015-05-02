<?php
/**
 * @file
 * PHPUnit test bootstrap.
 */
$autoloader = require __DIR__ . '/../../vendor/autoload.php';
// Add both src and test dirs to autoloader.
$autoloader->add('dcr', array(__DIR__ . '/../src', __DIR__ . '/../tests/phpunit'));
