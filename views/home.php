<?php

/** @var array $config */
/** @var array $user */
/** @var string $csrfToken */
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

<p>
    Prihlásený používateľ:
    <?= htmlspecialchars($user['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
</p>

<form method="post" action="/logout">
    <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
    >

    <button type="submit">Odhlásiť sa</button>
</form>
</body>
</html>