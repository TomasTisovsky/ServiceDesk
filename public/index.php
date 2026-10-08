<?php

use App\Core\Router;

$config = require dirname(__DIR__) . '/config/bootstrap.php';

header('Content-Type: text/html; charset=UTF-8');

$router = new Router();

$registerRoutes = require dirname(__DIR__) . '/config/routes.php';
$registerRoutes($router, $config);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!is_string($path)) {
    http_response_code(400);
    echo '400 – Neplatná požiadavka.';
    exit;
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $path);