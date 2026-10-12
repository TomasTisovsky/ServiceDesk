<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(
        string $method,
        string $path,
        callable $handler
    ): void {
        $quotedPath = preg_quote($path, '~');

        $pattern = str_replace(
            '\{id\}',
            '([1-9][0-9]*)',
            $quotedPath
        );

        $this->routes[$method][] = [
            'pattern' => '~^' . $pattern . '$~',
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches) !== 1) {
                continue;
            }

            array_shift($matches);

            ($route['handler'])(...$matches);
            return;
        }

        http_response_code(404);
        echo '404 – Stránka neexistuje.';
    }
}