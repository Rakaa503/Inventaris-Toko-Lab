<?php

declare(strict_types=1);

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        self::ensureSession();

        $token = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_string($token) || strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));
            $_SESSION[self::SESSION_KEY] = $token;
        }

        return $token;
    }

    public static function verify(string $token): bool
    {
        self::ensureSession();

        $knownToken = $_SESSION[self::SESSION_KEY] ?? null;

        if (
            !is_string($knownToken)
            || !preg_match('/^[a-f0-9]{64}$/', $knownToken)
            || !preg_match('/^[a-f0-9]{64}$/', $token)
        ) {
            return false;
        }

        return hash_equals($knownToken, $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="'
            . htmlspecialchars(
                self::token(),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            )
            . '">';
    }

    public static function regenerate(): string
    {
        self::ensureSession();

        $token = bin2hex(random_bytes(32));

        $_SESSION[self::SESSION_KEY] = $token;

        return $token;
    }

    private static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException(
                'Session harus aktif untuk menggunakan CSRF protection.'
            );
        }
    }
}