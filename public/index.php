<?php

use Illuminate\Http\Request;

// Silence E_DEPRECATED/E_NOTICE noise (e.g. PHP 8.5's PDO::MYSQL_ATTR_SSL_CA
// deprecation) so it doesn't clutter responses or logs in the browser.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
