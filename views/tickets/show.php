<?php
/** @var array $ticket */

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
$formatTime = static function (?string $value): string {
    if ($value === null) {
        return '—';
    }

    return (new DateTimeImmutable($value))
        ->setTimezone(new DateTimeZone('Europe/Bratislava'))
        ->format('d.m.Y H:i');
};
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail tiketu | ServiceDesk</title>
</head>
<body>
<main>
    <p><a href="/tickets">Späť na tikety</a></p>

    <h1>
        #<?= (int) $ticket['id'] ?>
        – <?= $escape($ticket['title']) ?>
    </h1>

    <dl>
        <dt>Autor</dt>
        <dd><?= $escape($ticket['author_name']) ?></dd>

        <dt>Zariadenie</dt>
        <dd><?= $escape($ticket['device_name']) ?></dd>

        <dt>Stav</dt>
        <dd><?= $escape($statuses[$ticket['status']]) ?></dd>

        <dt>Priorita</dt>
        <dd><?= $escape($priorities[$ticket['priority']]) ?></dd>

        <dt>Technik</dt>
        <dd><?= $escape($ticket['technician_name'] ?? 'Nepridelený') ?></dd>

        <dt>Vytvorené</dt>
        <dd><?= $escape($formatTime($ticket['created_at'])) ?></dd>

        <dt>Vyriešené</dt>
        <dd><?= $escape($formatTime($ticket['resolved_at'])) ?></dd>
    </dl>

    <h2>Opis problému</h2>
    <p><?= nl2br($escape($ticket['description'])) ?></p>

    <?php if ($ticket['resolution'] !== null): ?>
        <h2>Spôsob riešenia</h2>
        <p><?= nl2br($escape($ticket['resolution'])) ?></p>
    <?php endif; ?>
</main>
</body>
</html>