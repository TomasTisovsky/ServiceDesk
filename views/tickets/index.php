<?php
/** @var array $tickets */
/** @var array $user */

$escape = static fn(string $value): string =>
    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$statuses = [
    'NEW' => 'Nový',
    'IN_PROGRESS' => 'V riešení',
    'RESOLVED' => 'Vyriešený',
];

$priorities = [
    'LOW' => 'Nízka',
    'MEDIUM' => 'Stredná',
    'HIGH' => 'Vysoká',
];
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tikety | ServiceDesk</title>
</head>
<body>
    <main>
        <h1><?= $user['role'] === 'TECHNICIAN' ? 'Všetky tikety' : 'Moje tikety' ?></h1>

        <p><a href="/">Úvodná stránka</a></p>

        <?php if ($user['role'] === 'EMPLOYEE'): ?>
            <p><a href="/tickets/create">Vytvoriť tiket</a></p>
        <?php endif; ?>

        <?php if ($tickets === []): ?>
            <p>Zatiaľ tu nie sú žiadne tikety.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Názov</th>
                        <th>Autor</th>
                        <th>Zariadenie</th>
                        <th>Stav</th>
                        <th>Priorita</th>
                        <th>Technik</th>
                        <th>Vytvorené</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <?php
                        $createdAt = new DateTimeImmutable($ticket['created_at']);
                        $localTime = $createdAt->setTimezone(
                            new DateTimeZone('Europe/Bratislava')
                        );
                        ?>
                        <tr>
                            <td><?= (int) $ticket['id'] ?></td>
                            <td>
                                <a href="/tickets/<?= (int) $ticket['id'] ?>">
                                    <?= $escape($ticket['title']) ?>
                                </a>
                            </td>
                            <td><?= $escape($ticket['author_name']) ?></td>
                            <td><?= $escape($ticket['device_name']) ?></td>
                            <td><?= $escape($statuses[$ticket['status']]) ?></td>
                            <td><?= $escape($priorities[$ticket['priority']]) ?></td>
                            <td>
                                <?= $escape($ticket['technician_name'] ?? 'Nepridelený') ?>
                            </td>
                            <td><?= $escape($localTime->format('d.m.Y H:i')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>