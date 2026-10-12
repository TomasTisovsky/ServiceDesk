<?php


namespace App\Controllers;

use App\Core\Auth;
use App\Services\TicketService;
use App\Core\Csrf;

class TicketController
{
    public function __construct(private TicketService $ticketService)
    {
    }

    public function index(): void
    {
        Auth::requireLogin();

        $user = $_SESSION['user'];

        $tickets = $this->ticketService->listForUser(
            $user['id'],
            $user['role']
        );

        require dirname(__DIR__, 2) . '/views/tickets/index.php';
    }

    public function create(): void
    {
        Auth::requireLogin();

        $user = $_SESSION['user'];

        if ($user['role'] !== 'EMPLOYEE') {
            http_response_code(403);
            echo 'Tikety môže vytvárať iba zamestnanec.';
            return;
        }

        $devices = $this->ticketService->availableDevices(
            $user['id'],
            $user['role']
        );

        $csrfToken = Csrf::token();

        $errors = [];

        $data = [
            'title' => '',
            'description' => '',
            'device_id' => '',
            'priority' => 'MEDIUM',
        ];

        require dirname(__DIR__, 2) . '/views/tickets/create.php';
    }

    public function store(): void
    {
        Auth::requireLogin();

        $user = $_SESSION['user'];

        if ($user['role'] !== 'EMPLOYEE') {
            http_response_code(403);
            echo 'Tikety môže vytvárať iba zamestnanec.';
            return;
        }

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Neplatný bezpečnostný token. Obnov stránku.';
            return;
        }

        $data = [];

        foreach (['title', 'description', 'device_id', 'priority'] as $field) {
            $data[$field] = is_string($_POST[$field] ?? null)
                ? trim($_POST[$field])
                : '';
        }

        $errors = $this->ticketService->create(
            $user['id'],
            $user['role'],
            $data
        );

        if ($errors === []) {
            header('Location: /tickets', true, 303);
            exit;
        }

        http_response_code(422);

        $devices = $this->ticketService->availableDevices(
            $user['id'],
            $user['role']
        );

        $csrfToken = Csrf::token();

        require dirname(__DIR__, 2) . '/views/tickets/create.php';
    }

    public function show(string $id): void
    {
        Auth::requireLogin();

        $ticketId = filter_var(
            $id,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($ticketId === false) {
            http_response_code(404);
            echo 'Tiket sa nenašiel.';
            return;
        }

        $user = $_SESSION['user'];

        $ticket = $this->ticketService->detailForUser(
            $ticketId,
            $user['id'],
            $user['role']
        );

        if ($ticket === null) {
            http_response_code(404);
            echo 'Tiket sa nenašiel.';
            return;
        }

        require dirname(__DIR__, 2) . '/views/tickets/show.php';
    }

}