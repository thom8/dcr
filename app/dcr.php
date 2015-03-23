#!/usr/bin/env php
<?php
/**
 * @file
 * Main application runner.
 */

set_time_limit(0);

// Include the composer autoloader.
require_once __DIR__ . '/../vendor/autoload.php';

use dcr\DcrApplication;
use dcr\Commands\ReviewCommand;

define('DCR_APP_NAME', 'Drupal Code Review (DCR)');
define('DCR_APP_VERSION', '0.1');

// Init application.
$app = new DcrApplication(DCR_APP_NAME, DCR_APP_VERSION);
// Add review command.
$app->add(new ReviewCommand());
// Run app.
$app->run();
