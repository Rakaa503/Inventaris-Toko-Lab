<?php

declare(strict_types=1);

final class AuthMiddleware
{
    public function __construct(
        private AuthService $auth
    ) {
    }

    public function handle(callable $next): void
    {
        $user = $this->auth->currentUser();

        if ($user === null) {
            header('Location: ' . url('login'), true, 303);
            exit;
        }

        $next($user);
    }
}