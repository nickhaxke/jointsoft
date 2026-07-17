<?php

declare(strict_types=1);

/**
 * Front Controller
 * 
 * All requests are routed through this file.
 */

// Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Bootstrap and run the application
$app = new App\Core\App(dirname(__DIR__));
$app->boot();
