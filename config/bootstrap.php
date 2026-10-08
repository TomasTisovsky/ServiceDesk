<?php

use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->load();

date_default_timezone_set('UTC');

if (PHP_SAPI !== 'cli') {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',
        'use_strict_mode' => true,
    ]);
}

return [
    'app' => [
        'name' => $_ENV['APP_NAME'],
        'timezone' => $_ENV['APP_TIMEZONE'],
    ],
    'database' => [
        'host' => $_ENV['DB_HOST'],
        'port' => $_ENV['DB_PORT'],
        'service' => $_ENV['DB_SERVICE'],
        'user' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'],
    ],
];