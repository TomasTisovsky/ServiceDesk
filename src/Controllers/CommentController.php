<?php


namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Services\CommentService;
use App\Services\TicketService;
use InvalidArgumentException;

class CommentController
{
    public function __construct(
        private CommentService $comments,
        private TicketService  $tickets
    )
    {
    }

    public function store(string $id): void
    {
        Auth::requireLogin();

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Neplatný bezpečnostný token.';
            return;
        }

        $ticketId = filter_var(
            $id,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $user = $_SESSION['user'];

        if ($ticketId === false
            || $this->tickets->detailForUser(
                $ticketId,
                $user['id'],
                $user['role']
            ) === null) {
            http_response_code(404);
            echo 'Tiket sa nenašiel.';
            return;
        }

        $content = is_string($_POST['content'] ?? null)
            ? $_POST['content']
            : '';

        try {
            $success = $this->comments->add(
                $ticketId,
                $user['id'],
                $user['role'],
                $content
            );

            if (!$success) {
                $_SESSION['ticket_error'] =
                    'Komentár nemôžeš pridať. Over pridelenie a stav tiketu.';
            }
        } catch (InvalidArgumentException $exception) {
            $_SESSION['ticket_error'] = $exception->getMessage();
        }

        header('Location: /tickets/' . $ticketId, true, 303);
        exit;
    }
}