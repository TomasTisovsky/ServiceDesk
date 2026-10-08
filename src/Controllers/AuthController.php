<?php

namespace App\Controllers;

use App\Core\Csrf;
use App\Services\AuthService;

class AuthController
{
    public function __construct(private AuthService $auth)
    {
    }

    public function showLogin(): void
    {
        if (isset($_SESSION['user'])) {
            header('Location: /', true, 302);
            exit;
        }

        $error = null;
        $email = '';
        $csrfToken = Csrf::token();

        require dirname(__DIR__, 2) . '/views/auth/login.php';
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Neplatný bezpečnostný token. Obnov stránku.';
            return;
        }

        $email = is_string($_POST['email'] ?? null)
            ? trim($_POST['email'])
            : '';

        $password = is_string($_POST['password'] ?? null)
            ? $_POST['password']
            : '';

        if ($this->auth->login($email, $password)) {
            header('Location: /', true, 303);
            exit;
        }

        $error = 'Nesprávny e-mail alebo heslo.';
        $csrfToken = Csrf::token();

        require dirname(__DIR__, 2) . '/views/auth/login.php';
    }

    public function logout(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Neplatný bezpečnostný token. Obnov stránku.';
            return;
        }

        $this->auth->logout();

        header('Location: /login', true, 303);
        exit;
    }
}