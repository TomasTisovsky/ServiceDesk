<?php


namespace App\Core;

class Auth
{
    public static function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login', true, 302);
            exit;
        }
    }
}