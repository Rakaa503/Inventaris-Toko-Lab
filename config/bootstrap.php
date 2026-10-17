<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/core/Env.php';

Env::load(ROOT_PATH . '/.env');

date_default_timezone_set(
    Env::get('APP_TIMEZONE', 'Asia/Jakarta')
);

$baseUrl = rtrim(
    Env::get('APP_URL', 'http://127.0.0.1:8081'),
    '/'
);

if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
    throw new RuntimeException('APP_URL tidak valid.');
}

define('BASE_URL', $baseUrl);

$isHttps = !empty($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== 'off';

$secureCookie = Env::get('SESSION_SECURE_COOKIE', 'false') === 'true';

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_name(
        Env::get('SESSION_NAME', 'k3_inventory_session')
    );

    session_set_cookie_params([
        'httponly' => true,
        'secure' => $secureCookie || $isHttps,
        'samesite' => Env::get('SESSION_SAME_SITE', 'Lax'),
        'path' => '/',
    ]);

    session_start();
}

function e(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function url(string $path = ''): string
{
    return BASE_URL . (
        $path === '' ? '/' : '/' . ltrim($path, '/')
    );
}