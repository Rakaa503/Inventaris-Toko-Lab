<?php

declare(strict_types=1);

final class App
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $path = $this->normalizePath($path);
        $key = $method . ' ' . $path;

        if (isset($this->routes[$key])) {
            throw new LogicException('Route duplikat: ' . $key);
        }

        $this->routes[$key] = $handler;
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '' ? '/' : $path;
    }

    public function run(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path)) {
            http_response_code(400);
            echo '400 Bad Request';
            return;
        }

        $path = $this->normalizePath($path);
        $key = $method . ' ' . $path;

        if (isset($this->routes[$key])) {
            ($this->routes[$key])();
            return;
        }

        foreach ($this->routes as $routeKey => $_handler) {
            $parts = explode(' ', $routeKey, 2);

            if (count($parts) === 2 && $parts[1] === $path) {
                header('Allow: ' . $this->allowedMethods($path));
                http_response_code(405);
                echo '405 Method Not Allowed';
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    private function allowedMethods(string $path): string
    {
        $methods = [];

        foreach (array_keys($this->routes) as $routeKey) {
            $parts = explode(' ', $routeKey, 2);

            if (count($parts) === 2 && $parts[1] === $path) {
                $methods[] = $parts[0];
            }
        }

        return implode(', ', array_unique($methods));
    }
}