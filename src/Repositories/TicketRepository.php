<?php


namespace App\Repositories;

use App\Core\Database;
use InvalidArgumentException;
use RuntimeException;

class TicketRepository
{
    public function __construct(private Database $database)
    {
    }

    public function findVisibleTo(int $userId, string $role): array
    {
        if (!in_array($role, ['EMPLOYEE', 'TECHNICIAN'], true)) {
            throw new InvalidArgumentException('Neplatná rola.');
        }

        $sql = '
            SELECT
                t.id,
                t.title,
                t.status,
                t.priority,
                TO_CHAR(
                    t.created_at,
                    \'YYYY-MM-DD"T"HH24:MI:SS"Z"\'
                ) AS created_at,
                d.name AS device_name,
                a.name AS author_name,
                tech.name AS technician_name
            FROM tickets t
            JOIN devices d ON d.id = t.device_id
            JOIN users a ON a.id = t.author_id
            LEFT JOIN users tech ON tech.id = t.technician_id
        ';

        if ($role === 'EMPLOYEE') {
            $sql .= ' WHERE t.author_id = :user_id';
        }

        $sql .= ' ORDER BY t.created_at DESC, t.id DESC';

        $connection = $this->database->connection();
        $statement = oci_parse($connection, $sql);

        if ($statement === false) {
            throw new RuntimeException('Príprava zoznamu tiketov zlyhala.');
        }

        try {
            if ($role === 'EMPLOYEE') {
                if (!oci_bind_by_name($statement, ':user_id', $userId)) {
                    throw new RuntimeException('Naviazanie používateľa zlyhalo.');
                }
            }

            if (!oci_execute($statement, OCI_NO_AUTO_COMMIT)) {
                throw new RuntimeException('Načítanie tiketov zlyhalo.');
            }

            $tickets = [];

            while (($row = oci_fetch_assoc($statement)) !== false) {
                $tickets[] = array_change_key_case($row, CASE_LOWER);
            }

            if (oci_error($statement) !== false) {
                throw new RuntimeException('Čítanie tiketov zlyhalo.');
            }

            return $tickets;
        } finally {
            oci_free_statement($statement);
        }
    }

    public function create(
        string $title,
        string $description,
        int $authorId,
        int $deviceId,
        string $priority
    ): void {
        $connection = $this->database->connection();

        $statement = oci_parse(
            $connection,
            'INSERT INTO tickets (
            title, description, author_id, device_id, priority
         ) VALUES (
            :title, :description, :author_id, :device_id, :priority
         )'
        );

        if ($statement === false) {
            throw new RuntimeException('Príprava vytvorenia tiketu zlyhala.');
        }

        try {
            $parameters = [
                ':title' => $title,
                ':description' => $description,
                ':author_id' => $authorId,
                ':device_id' => $deviceId,
                ':priority' => $priority,
            ];

            foreach ($parameters as $name => &$value) {
                if (!oci_bind_by_name($statement, $name, $value)) {
                    throw new RuntimeException('Naviazanie údajov zlyhalo.');
                }
            }
            unset($value);

            if (!oci_execute($statement, OCI_NO_AUTO_COMMIT)) {
                throw new RuntimeException('Uloženie tiketu zlyhalo.');
            }

            if (!oci_commit($connection)) {
                throw new RuntimeException('Potvrdenie uloženia zlyhalo.');
            }
        } catch (\Throwable $exception) {
            oci_rollback($connection);
            throw $exception;
        } finally {
            oci_free_statement($statement);
        }
    }

    public function findById(int $id): ?array
    {
        $connection = $this->database->connection();

        $statement = oci_parse(
            $connection,
            'SELECT
            t.id, t.technician_id,t.title, t.description,
            t.author_id, t.status, t.priority,
            t.resolution,             t.resolution,
            TO_CHAR(
                t.created_at,
                \'YYYY-MM-DD"T"HH24:MI:SS"Z"\'
            ) AS created_at,
            TO_CHAR(
                t.resolved_at,
                \'YYYY-MM-DD"T"HH24:MI:SS"Z"\'
            ) AS resolved_at,
            d.name AS device_name,
            d.name AS device_name,
            a.name AS author_name,
            tech.name AS technician_name
         FROM tickets t
         JOIN devices d ON d.id = t.device_id
         JOIN users a ON a.id = t.author_id
         LEFT JOIN users tech ON tech.id = t.technician_id
         WHERE t.id = :id'
        );

        if ($statement === false) {
            throw new RuntimeException('Príprava detailu zlyhala.');
        }

        try {
            if (!oci_bind_by_name($statement, ':id', $id)
                || !oci_execute($statement, OCI_NO_AUTO_COMMIT)) {
                throw new RuntimeException('Načítanie detailu zlyhalo.');
            }

            // OCI_RETURN_LOBS načíta CLOB ako text.
            $row = oci_fetch_array(
                $statement,
                OCI_ASSOC | OCI_RETURN_NULLS | OCI_RETURN_LOBS
            );

            if ($row === false) {
                if (oci_error($statement) !== false) {
                    throw new RuntimeException('Čítanie detailu zlyhalo.');
                }

                return null;
            }

            return array_change_key_case($row, CASE_LOWER);
        } finally {
            oci_free_statement($statement);
        }
    }

    public function claim(int $ticketId, int $technicianId): bool
    {
        return $this->executeChange(
            "UPDATE tickets
         SET technician_id = :technician_id,
             status = 'IN_PROGRESS'
         WHERE id = :ticket_id
           AND status = 'NEW'
           AND technician_id IS NULL",
            [
                ':ticket_id' => $ticketId,
                ':technician_id' => $technicianId,
            ]
        );
    }

    public function changePriority(
        int $ticketId,
        int $technicianId,
        string $priority
    ): bool {
        return $this->executeChange(
            "UPDATE tickets
         SET priority = :priority
         WHERE id = :ticket_id
           AND technician_id = :technician_id
           AND status = 'IN_PROGRESS'",
            [
                ':ticket_id' => $ticketId,
                ':technician_id' => $technicianId,
                ':priority' => $priority,
            ]
        );
    }

    public function resolve(
        int $ticketId,
        int $technicianId,
        string $resolution
    ): bool {
        return $this->executeChange(
            "UPDATE tickets
         SET resolution = :resolution,
             resolved_at = SYS_EXTRACT_UTC(SYSTIMESTAMP),
             status = 'RESOLVED'
         WHERE id = :ticket_id
           AND technician_id = :technician_id
           AND status = 'IN_PROGRESS'",
            [
                ':ticket_id' => $ticketId,
                ':technician_id' => $technicianId,
                ':resolution' => $resolution,
            ]
        );
    }

    private function executeChange(string $sql, array $parameters): bool
    {
        $connection = $this->database->connection();
        $statement = oci_parse($connection, $sql);

        if ($statement === false) {
            throw new RuntimeException('Príprava zmeny tiketu zlyhala.');
        }

        try {
            foreach ($parameters as $name => &$value) {
                if (!oci_bind_by_name($statement, $name, $value)) {
                    throw new RuntimeException('Naviazanie údajov zlyhalo.');
                }
            }
            unset($value);

            if (!oci_execute($statement, OCI_NO_AUTO_COMMIT)) {
                throw new RuntimeException('Zmena tiketu zlyhala.');
            }

            if (oci_num_rows($statement) !== 1) {
                oci_rollback($connection);
                return false;
            }

            if (!oci_commit($connection)) {
                throw new RuntimeException('Potvrdenie zmeny zlyhalo.');
            }

            return true;
        } catch (\Throwable $exception) {
            oci_rollback($connection);
            throw $exception;
        } finally {
            oci_free_statement($statement);
        }
    }

}