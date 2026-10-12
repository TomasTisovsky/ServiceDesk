<?php
/** @var array $ticket */
/** @var array $user */
/** @var string $csrfToken */
/** @var string|null $error */
/** @var array $comments */



$isTechnician = $user['role'] === 'TECHNICIAN';

$isAssignedTechnician = $isTechnician
    && $ticket['technician_id'] !== null
    && (int) $ticket['technician_id'] === (int) $user['id'];

$canComment = $ticket['status'] !== 'RESOLVED'
    && (
        ($user['role'] === 'EMPLOYEE'
            && (int) $ticket['author_id'] === (int) $user['id'])
        || $isAssignedTechnician
    );

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
<h2>Komentáre</h2>

<?php if ($comments === []): ?>
    <p>Zatiaľ bez komentárov.</p>
<?php else: ?>
    <?php foreach ($comments as $comment): ?>
        <article>
            <p>
                <strong><?= $escape($comment['author_name']) ?></strong>
                – <?= $escape($formatTime($comment['created_at'])) ?>
            </p>

            <p><?= nl2br($escape($comment['content'])) ?></p>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php if ($canComment): ?>
    <form
            method="post"
            action="/tickets/<?= (int) $ticket['id'] ?>/comments"
    >
        <input
                type="hidden"
                name="csrf_token"
                value="<?= $escape($csrfToken) ?>"
        >

        <label for="content">Nový komentár</label>
        <textarea id="content" name="content" rows="4" required></textarea>

        <button type="submit">Pridať komentár</button>
    </form>
<?php endif; ?>
<main>
    <p><a href="/tickets">Späť na tikety</a></p>

    <h1>
        #<?= (int) $ticket['id'] ?>
        – <?= $escape($ticket['title']) ?>
        <?php if ($error !== null): ?>
            <p role="alert"><?= $escape($error) ?></p>
        <?php endif; ?>
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

    <?php if ($isTechnician && $ticket['status'] === 'NEW'): ?>
        <h2>Prevzatie tiketu</h2>

        <form
                method="post"
                action="/tickets/<?= (int) $ticket['id'] ?>/claim"
        >
            <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $escape($csrfToken) ?>"
            >

            <button type="submit">Prevziať tiket</button>
        </form>
    <?php endif; ?>

    <?php if ($isAssignedTechnician
        && $ticket['status'] === 'IN_PROGRESS'): ?>

        <h2>Zmena priority</h2>

        <form
                method="post"
                action="/tickets/<?= (int) $ticket['id'] ?>/priority"
        >
            <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $escape($csrfToken) ?>"
            >

            <label for="priority">Priorita</label>

            <select id="priority" name="priority" required>
                <?php foreach ($priorities as $value => $label): ?>
                    <option
                            value="<?= $escape($value) ?>"
                        <?= $ticket['priority'] === $value ? 'selected' : '' ?>
                    >
                        <?= $escape($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Uložiť prioritu</button>
        </form>

        <h2>Vyriešenie tiketu</h2>

        <form
                method="post"
                action="/tickets/<?= (int) $ticket['id'] ?>/resolve"
        >
            <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $escape($csrfToken) ?>"
            >

            <label for="resolution">Spôsob riešenia</label>

            <textarea
                    id="resolution"
                    name="resolution"
                    rows="6"
                    required
            ></textarea>

            <button type="submit">Označiť ako vyriešený</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>