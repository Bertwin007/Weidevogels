<?php

declare(strict_types=1);

$staticTest = __DIR__.'/test.html';

if (! is_readable(__DIR__.'/../vendor/autoload.php') && is_readable($staticTest)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($staticTest);
    exit;
}

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
