<?php


use App\Core\Router;
use App\Controllers\AuthController;
use App\Core\Database;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Core\Auth;
use App\Core\Csrf;

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

    $router->get('/login', [$authController, 'showLogin']);
    $router->post('/login', [$authController, 'login']);

    $router->post('/logout', [$authController, 'logout']);
};