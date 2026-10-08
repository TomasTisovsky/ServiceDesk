<?php


require __DIR__ . '/vendor/autoload.php';

$app = new \App\Core\Application();

echo $app->name() . PHP_EOL;