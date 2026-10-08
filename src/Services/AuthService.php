<?php


namespace App\Services;

use App\Repositories\UserRepository;
use RuntimeException;

class AuthService
{
    public function __construct(private UserRepository $users)
    {
    }

    public function login(string $email, string $password): bool
    {
        $email = mb_strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return false;
        }

        $user = $this->users->findByEmail($email);

        if ($user === null
            || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        if (!session_regenerate_id(true)) {
            throw new RuntimeException('Obnovenie session zlyhalo.');
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];

        unset($_SESSION['csrf_token']);

        return true;
    }
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }
}