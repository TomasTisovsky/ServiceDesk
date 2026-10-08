<?php


use App\Core\Router;

return function (Router $router, array $config): void {
    $router->get('/', function () use ($config): void {
        require dirname(__DIR__) . '/views/home.php';
    });
};