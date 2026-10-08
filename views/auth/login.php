<?php
/** @var string|null $error */
/** @var string $email */
/** @var string $csrfToken */
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prihlásenie | ServiceDesk</title>
</head>
<body>
    <main>
        <h1>Prihlásenie</h1>

        <?php if ($error !== null): ?>
            <p role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </p>
        <?php endif; ?>
        <form method="post" action="/login">
            <div>
                <label for="email">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    autocomplete="username"
                    required
                    value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    >
                <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div>
                <label for="password">Heslo</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                    >
            </div>

            <button type="submit">Prihlásiť sa</button>
        </form>
    </main>
</body>
</html>