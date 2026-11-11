<?php

declare(strict_types=1);

$makeAuthController = static function (): AuthController {
    $pdo = Database::getConnection();

    $users = new UserRepository($pdo);
    $authService = new AuthService($users);
    $rateLimiter = new RateLimiter();

    return new AuthController(
        $authService,
        $rateLimiter
    );
};

$app->get('/login', static function () use ($makeAuthController): void {
    $makeAuthController()->showLogin();
});

$app->post('/login', static function () use ($makeAuthController): void {
    $makeAuthController()->login();
});

$app->post('/logout', static function () use ($makeAuthController): void {
    $makeAuthController()->logout();
});

$app->get('/', static function (): void {
    $pdo = Database::getConnection();

    $authService = new AuthService(
        new UserRepository($pdo)
    );

    $middleware = new AuthMiddleware($authService);

    $middleware->handle(
        static function (User $user): void {
            (new DashboardController())->index($user);
        }
    );
});