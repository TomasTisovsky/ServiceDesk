<?php


use App\Core\Database;
use App\Repositories\UserRepository;

$config = require __DIR__ . '/config/bootstrap.php';

$database = new Database($config['database']);
$repository = new UserRepository($database);

$user = $repository->findByEmail('tomas@example.test');

if ($user === null) {
    exit("Používateľ sa nenašiel.\n");
}

echo $user['name'] . PHP_EOL;
echo $user['role'] . PHP_EOL;