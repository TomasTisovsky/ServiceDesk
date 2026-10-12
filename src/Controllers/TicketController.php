<?php


namespace App\Controllers;

use App\Core\Auth;
use App\Services\TicketService;
use App\Core\Csrf;
use App\Repositories\CommentRepository;

class TicketController
{
    public function __construct(
        private TicketService $ticketService,
        private CommentRepository $commentRepository
    ) {
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

        $csrfToken = Csrf::token();

        $error = $_SESSION['ticket_error'] ?? null;
        unset($_SESSION['ticket_error']);

        $comments = $this->commentRepository->findForTicket($ticketId);

        require dirname(__DIR__, 2) . '/views/tickets/show.php';
    }

    public function claim(string $id): void
    {
        $this->handleTechnicianAction(
            $id,
            fn(int $ticketId, int $userId): bool =>
            $this->ticketService->claim(
                $ticketId,
                $userId,
                'TECHNICIAN'
            )
        );
    }

    public function changePriority(string $id): void
    {
        $priority = is_string($_POST['priority'] ?? null)
            ? $_POST['priority']
            : '';

        $this->handleTechnicianAction(
            $id,
            fn(int $ticketId, int $userId): bool =>
            $this->ticketService->changePriority(
                $ticketId,
                $userId,
                'TECHNICIAN',
                $priority
            )
        );
    }

    public function resolve(string $id): void
    {
        $resolution = is_string($_POST['resolution'] ?? null)
            ? $_POST['resolution']
            : '';

        $this->handleTechnicianAction(
            $id,
            fn(int $ticketId, int $userId): bool =>
            $this->ticketService->resolve(
                $ticketId,
                $userId,
                'TECHNICIAN',
                $resolution
            )
        );
    }

    private function handleTechnicianAction(
        string $id,
        callable $action
    ): void {
        Auth::requireLogin();

        $user = $_SESSION['user'];

        if ($user['role'] !== 'TECHNICIAN') {
            http_response_code(403);
            echo 'Akcia je dostupná iba technikovi.';
            return;
        }

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Neplatný bezpečnostný token. Obnov stránku.';
            return;
        }

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

        // Technik môže čítať všetky tikety.
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

        try {
            $success = $action($ticketId, $user['id']);
        } catch (\InvalidArgumentException $exception) {
            $_SESSION['ticket_error'] = $exception->getMessage();

            header('Location: /tickets/' . $ticketId, true, 303);
            exit;
        }

        if (!$success) {
            $_SESSION['ticket_error'] =
                'Zmenu nemožno vykonať. Tiket už zmenil stav '
                . 'alebo je pridelený inému technikovi.';
        }

        header('Location: /tickets/' . $ticketId, true, 303);
        exit;
    }


}