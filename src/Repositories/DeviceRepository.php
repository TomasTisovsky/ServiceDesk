<?php


namespace App\Repositories;

use App\Core\Database;
use RuntimeException;

class DeviceRepository
{
    public function __construct(private Database $database)
    {
    }

    public function findAvailableTo(int $userId): array
    {
        $connection = $this->database->connection();

        $statement = oci_parse(
            $connection,
            'SELECT id, name, description, assigned_user_id
             FROM devices
             WHERE assigned_user_id = :user_id
                OR assigned_user_id IS NULL
             ORDER BY name, id'
        );

        if ($statement === false) {
            throw new RuntimeException('Príprava zoznamu zariadení zlyhala.');
        }

        try {
            if (!oci_bind_by_name($statement, ':user_id', $userId)
                || !oci_execute($statement, OCI_NO_AUTO_COMMIT)) {
                throw new RuntimeException('Načítanie zariadení zlyhalo.');
            }

            $devices = [];

            while (($row = oci_fetch_assoc($statement)) !== false) {
                $devices[] = array_change_key_case($row, CASE_LOWER);
            }

            if (oci_error($statement) !== false) {
                throw new RuntimeException('Čítanie zariadení zlyhalo.');
            }

            return $devices;
        } finally {
            oci_free_statement($statement);
        }
    }
}