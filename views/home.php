<?php

/** @var array $config */
$appName = htmlspecialchars(
    $config['app']['name'],
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title><?= $appName ?></title>
</head>
<body>
<h1><?= $appName ?></h1>
<p>Základ aplikácie funguje.</p>
</body>
</html>