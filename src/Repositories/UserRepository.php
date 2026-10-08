<?php


namespace App\Repositories;

use App\Core\Database;
use RuntimeException;

class UserRepository
{
    public function __construct(private Database $database)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $connection = $this->database->connection();

        $statement = oci_parse(
            $connection,
            'SELECT id, name, email, password_hash, role
             FROM users
             WHERE email = :email'
        );

        if ($statement === false) {
            throw new RuntimeException('Príprava dotazu zlyhala.');
        }

        try {
            if (!oci_bind_by_name($statement, ':email', $email)) {
                throw new RuntimeException('Naviazanie e-mailu zlyhalo.');
            }

            if (!oci_execute($statement, OCI_NO_AUTO_COMMIT)) {
                throw new RuntimeException('Načítanie používateľa zlyhalo.');
            }

            $row = oci_fetch_assoc($statement);

            if ($row === false) {
                if (oci_error($statement) !== false) {
                    throw new RuntimeException('Čítanie výsledku zlyhalo.');
                }

                return null;
            }

            return array_change_key_case($row, CASE_LOWER);
        } finally {
            oci_free_statement($statement);
        }
    }
}