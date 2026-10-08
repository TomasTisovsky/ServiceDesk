<?php


$password = getenv('SERVICEDESK_DB_PASSWORD');

if ($password === false || $password === '') {
    exit("Chýba premenná SERVICEDESK_DB_PASSWORD.\n");
}

$connection = oci_connect(
    'servicedesk',
    $password,
    '127.0.0.1:1521/FREEPDB1',
    'AL32UTF8'
);

if ($connection === false) {
    $error = oci_error();
    exit("Pripojenie zlyhalo: " . $error['message'] . "\n");
}

$statement = oci_parse(
    $connection,
    'SELECT USER AS DB_USER, SYSDATE AS DB_TIME FROM dual'
);

if ($statement === false || !oci_execute($statement)) {
    $error = $statement === false
        ? oci_error($connection)
        : oci_error($statement);

    exit("Dotaz zlyhal: " . $error['message'] . "\n");
}

$row = oci_fetch_assoc($statement);

echo "Spojenie funguje.\n";
echo "Používateľ: " . $row['DB_USER'] . "\n";
echo "Dátum databázy: " . $row['DB_TIME'] . "\n";

oci_free_statement($statement);
oci_close($connection);