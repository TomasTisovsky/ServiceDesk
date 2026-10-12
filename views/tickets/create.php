<?php
/** @var array $devices */
/** @var string $csrfToken */
/** @var array $errors */
/** @var array $data */

$escape = static fn(string $value): string =>
    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nový tiket | ServiceDesk</title>
</head>
<body>
    <main>
        <h1>Nový tiket</h1>
        <p><a href="/tickets">Späť na tikety</a></p>

        <?php if ($errors !== []): ?>
            <ul role="alert">
                <?php foreach ($errors as $error): ?>
                    <li><?= $escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="/tickets">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= $escape($csrfToken) ?>"
            >

            <div>
                <label for="title">Názov problému</label>
                <input
                    value="<?= $escape($data['title']) ?>"
                    type="text"
                    id="title"
                    name="title"
                    maxlength="200"
                    required
                >
            </div>

            <div>
                <label for="description">Opis problému</label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    required
                ><?= $escape($data['description']) ?></textarea>
            </div>

            <div>
                <label for="device_id">Zariadenie</label>
                <select id="device_id" name="device_id" required>
                    <option value="">Vyber zariadenie</option>

                    <?php foreach ($devices as $device): ?>
                        <option <?= (string) $device['id'] === $data['device_id'] ? 'selected' : '' ?> value="<?= (int) $device['id'] ?>">
                            <?= $escape($device['name']) ?>
                            (#<?= (int) $device['id'] ?>)
                            <?= $device['assigned_user_id'] === null
                                ? '– spoločné'
                                : '– pridelené tebe' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="priority">Priorita</label>
                <select id="priority" name="priority" required>
                    <option value="LOW" <?= $data['priority'] === 'LOW' ? 'selected' : '' ?>>
                        Nízka
                    </option>
                    <option value="MEDIUM" <?= $data['priority'] === 'MEDIUM' ? 'selected' : '' ?>>
                        Stredná
                    </option>
                    <option value="HIGH" <?= $data['priority'] === 'HIGH' ? 'selected' : '' ?>>
                        Vysoká
                    </option>
                </select>
            </div>

            <button type="submit">Vytvoriť tiket</button>
        </form>
    </main>
</body>
</html>