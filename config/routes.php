<?php


use App\Core\Router;
use App\Controllers\AuthController;
use App\Core\Database;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Core\Auth;
use App\Core\Csrf;
use App\Controllers\TicketController;
use App\Repositories\TicketRepository;
use App\Services\TicketService;
use App\Repositories\DeviceRepository;

return function (Router $router, array $config): void {
    $router->get('/', function () use ($config): void {
        Auth::requireLogin();

        $user = $_SESSION['user'];
        $csrfToken = Csrf::token();

        require dirname(__DIR__) . '/views/home.php';
    });
    $database = new Database($config['database']);
    $userRepository = new UserRepository($database);
    $authService = new AuthService($userRepository);
    $authController = new AuthController($authService);
    $ticketRepository = new TicketRepository($database);
    $deviceRepository = new DeviceRepository($database);
    $ticketService = new TicketService(
        $ticketRepository,
        $deviceRepository
    );
    $ticketController = new TicketController($ticketService);

    $router->get('/tickets', [$ticketController, 'index']);
    $router->get('/tickets/create', [$ticketController, 'create']);
    $router->post('/tickets', [$ticketController, 'store']);

    $router->get('/login', [$authController, 'showLogin']);
    $router->post('/login', [$authController, 'login']);

    $router->post('/logout', [$authController, 'logout']);
    $router->get('/tickets/{id}', [$ticketController, 'show']);

    $router->post(
        '/tickets/{id}/claim',
        [$ticketController, 'claim']
    );

    $router->post(
        '/tickets/{id}/priority',
        [$ticketController, 'changePriority']
    );

    $router->post(
        '/tickets/{id}/resolve',
        [$ticketController, 'resolve']
    );


};