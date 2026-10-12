<?php


namespace App\Repositories;

use App\Core\Database;
use RuntimeException;
use Throwable;

class CommentRepository
{
    public function __construct(private Database $database)
    {
    }

    public function findForTicket(int $ticketId): array
    {
        $connection = $this->database->connection();

        $statement = oci_parse(
            $connection,
            'SELECT
                c.id,
                c.content,
                u.name AS author_name,
                TO_CHAR(
                    c.created_at,
                    \'YYYY-MM-DD"T"HH24:MI:SS"Z"\'
                ) AS created_at
             FROM ticket_comments c
             JOIN users u ON u.id = c.author_id
             WHERE c.ticket_id = :ticket_id
             ORDER BY c.created_at, c.id'
        );

        if ($statement === false) {
            throw new RuntimeException('Príprava komentárov zlyhala.');
        }

        try {
            if (!oci_bind_by_name($statement, ':ticket_id', $ticketId)
                || !oci_execute($statement, OCI_NO_AUTO_COMMIT)) {
                throw new RuntimeException('Načítanie komentárov zlyhalo.');
            }

            $comments = [];

            while (($row = oci_fetch_array(
                    $statement,
                    OCI_ASSOC | OCI_RETURN_NULLS | OCI_RETURN_LOBS
                )) !== false) {
                $comments[] = array_change_key_case($row, CASE_LOWER);
            }

            if (oci_error($statement) !== false) {
                throw new RuntimeException('Čítanie komentárov zlyhalo.');
            }

            return $comments;
        } finally {
            oci_free_statement($statement);
        }
    }

    public function add(
        int    $ticketId,
        int    $userId,
        string $role,
        string $content
    ): bool
    {
        $connection = $this->database->connection();
        $lock = null;
        $insert = null;

        try {
            // Zámok koordinuje komentovanie s vyriešením tiketu.
            $lock = oci_parse(
                $connection,
                'SELECT author_id, technician_id, status
                 FROM tickets
                 WHERE id = :ticket_id
                 FOR UPDATE'
            );

            if ($lock === false) {
                throw new RuntimeException('Príprava zámku zlyhala.');
            }

            if (!oci_bind_by_name($lock, ':ticket_id', $ticketId)
                || !oci_execute($lock, OCI_NO_AUTO_COMMIT)) {
                throw new RuntimeException('Uzamknutie tiketu zlyhalo.');
            }

            $ticket = oci_fetch_array($lock, OCI_ASSOC | OCI_RETURN_NULLS);

            if ($ticket === false && oci_error($lock) !== false) {
                throw new RuntimeException('Čítanie tiketu zlyhalo.');
            }

            $allowed = $ticket !== false
                && $ticket['STATUS'] !== 'RESOLVED'
                && (
                    ($role === 'EMPLOYEE'
                        && (int)$ticket['AUTHOR_ID'] === $userId)
                    ||
                    ($role === 'TECHNICIAN'
                        && $ticket['TECHNICIAN_ID'] !== null
                        && (int)$ticket['TECHNICIAN_ID'] === $userId)
                );

            if (!$allowed) {
                oci_rollback($connection);
                return false;
            }

            $insert = oci_parse(
                $connection,
                'INSERT INTO ticket_comments (ticket_id, author_id, content)
                 VALUES (:ticket_id, :author_id, :content)'
            );

            if ($insert === false) {
                throw new RuntimeException('Príprava komentára zlyhala.');
            }

            if (!oci_bind_by_name($insert, ':ticket_id', $ticketId)
                || !oci_bind_by_name($insert, ':author_id', $userId)
                || !oci_bind_by_name($insert, ':content', $content)
                || !oci_execute($insert, OCI_NO_AUTO_COMMIT)) {
                throw new RuntimeException('Uloženie komentára zlyhalo.');
            }

            if (!oci_commit($connection)) {
                throw new RuntimeException('Potvrdenie komentára zlyhalo.');
            }

            return true;
        } catch (Throwable $exception) {
            oci_rollback($connection);
            throw $exception;
        } finally {
            if ($insert !== null && $insert !== false) {
                oci_free_statement($insert);
            }

            if ($lock !== null && $lock !== false) {
                oci_free_statement($lock);
            }
        }
    }
}