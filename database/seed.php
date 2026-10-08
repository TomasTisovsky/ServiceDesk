<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Skript je určený iba pre terminál.');
}

$config = require dirname(__DIR__) . '/config/bootstrap.php';
$db = $config['database'];

$connectionString = sprintf(
    '%s:%s/%s',
    $db['host'],
    $db['port'],
    $db['service']
);

$connection = oci_connect(
    $db['user'],
    $db['password'],
    $connectionString,
    'AL32UTF8'
);

if ($connection === false) {
    $error = oci_error();
    fwrite(STDERR, "Pripojenie zlyhalo: {$error['message']}\n");
    exit(1);
}

/**
 * Pripraví a vykoná dotaz bez automatického commitu.
 */
function executeStatement($connection, string $sql, array $parameters = [])
{
    $statement = oci_parse($connection, $sql);

    if ($statement === false) {
        $error = oci_error($connection);
        throw new RuntimeException($error['message']);
    }

    try {
        foreach ($parameters as $name => &$value) {
            if (!oci_bind_by_name($statement, $name, $value)) {
                $error = oci_error($statement);
                throw new RuntimeException($error['message']);
            }
        }
        unset($value);

        if (!oci_execute($statement, OCI_NO_AUTO_COMMIT)) {
            $error = oci_error($statement);
            throw new RuntimeException($error['message']);
        }

        return $statement;
    } catch (Throwable $exception) {
        oci_free_statement($statement);
        throw $exception;
    }
}

$users = [
    [
        'name' => 'Tomáš Tisovský',
        'email' => 'tomas@example.test',
        'role' => 'EMPLOYEE',
    ],
    [
        'name' => 'Jana Nováková',
        'email' => 'jana@example.test',
        'role' => 'EMPLOYEE',
    ],
    [
        'name' => 'Peter Kováč',
        'email' => 'peter@example.test',
        'role' => 'TECHNICIAN',
    ],
];

// Heslo iba pre lokálne ukážkové účty.
$demoPassword = 'Demo12345!';

$devices = [
    [
        'name' => 'Lenovo ThinkPad T14',
        'description' => 'Pracovný notebook, inventárne označenie NB-001.',
        'owner' => 'tomas@example.test',
    ],
    [
        'name' => 'Dell OptiPlex 7090',
        'description' => 'Stolný počítač, inventárne označenie PC-001.',
        'owner' => 'tomas@example.test',
    ],
    [
        'name' => 'Samsung Galaxy A54',
        'description' => 'Služobný telefón, inventárne označenie MOB-001.',
        'owner' => 'tomas@example.test',
    ],
    [
        'name' => 'HP ProBook 450',
        'description' => 'Pracovný notebook, inventárne označenie NB-002.',
        'owner' => 'jana@example.test',
    ],
    [
        'name' => 'Dell OptiPlex 7090',
        'description' => 'Stolný počítač, inventárne označenie PC-002.',
        'owner' => 'jana@example.test',
    ],
    [
        'name' => 'HP LaserJet Pro',
        'description' => 'Spoločná tlačiareň na chodbe.',
        'owner' => null,
    ],
    [
        'name' => 'Epson EB-FH06',
        'description' => 'Spoločný projektor v zasadacej miestnosti.',
        'owner' => null,
    ],
    [
        'name' => 'Brother ADS-1700W',
        'description' => 'Spoločný skener v kancelárii.',
        'owner' => null,
    ],
];

try {
    $statement = executeStatement(
        $connection,
        'SELECT
            (SELECT COUNT(*) FROM users) AS USER_COUNT,
            (SELECT COUNT(*) FROM devices) AS DEVICE_COUNT
         FROM dual'
    );

    $counts = oci_fetch_assoc($statement);
    oci_free_statement($statement);

    if ($counts === false) {
        throw new RuntimeException('Nepodarilo sa overiť existujúce dáta.');
    }

    if ((int) $counts['USER_COUNT'] > 0 || (int) $counts['DEVICE_COUNT'] > 0) {
        throw new RuntimeException(
            'Seed vyžaduje prázdne tabuľky users a devices. Dáta sa nezmenili.'
        );
    }

    foreach ($users as $user) {
        $statement = executeStatement(
            $connection,
            'INSERT INTO users (name, email, password_hash, role)
             VALUES (:name, :email, :password_hash, :role)',
            [
                ':name' => $user['name'],
                ':email' => $user['email'],
                ':password_hash' => password_hash(
                    $demoPassword,
                    PASSWORD_DEFAULT
                ),
                ':role' => $user['role'],
            ]
        );

        oci_free_statement($statement);
    }

    // ID načítame z databázy; nepredpokladáme, že začínajú od 1.
    $statement = executeStatement(
        $connection,
        'SELECT id, email FROM users'
    );

    $userIds = [];

    while ($row = oci_fetch_assoc($statement)) {
        $userIds[$row['EMAIL']] = (int) $row['ID'];
    }

    oci_free_statement($statement);

    foreach ($devices as $device) {
        $ownerId = $device['owner'] === null
            ? null
            : $userIds[$device['owner']];

        $statement = executeStatement(
            $connection,
            'INSERT INTO devices (name, description, assigned_user_id)
             VALUES (:name, :description, :assigned_user_id)',
            [
                ':name' => $device['name'],
                ':description' => $device['description'],
                ':assigned_user_id' => $ownerId,
            ]
        );

        oci_free_statement($statement);
    }

    if (!oci_commit($connection)) {
        $error = oci_error($connection);
        throw new RuntimeException($error['message']);
    }

    echo "Vytvorení používatelia: " . count($users) . PHP_EOL;
    echo "Vytvorené zariadenia: " . count($devices) . PHP_EOL;
    echo "Heslo ukážkových účtov: {$demoPassword}" . PHP_EOL;
} catch (Throwable $exception) {
    oci_rollback($connection);
    fwrite(STDERR, "Seed zlyhal: {$exception->getMessage()}\n");
    oci_close($connection);
    exit(1);
}

oci_close($connection);