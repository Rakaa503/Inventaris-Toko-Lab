<?php
declare(strict_types=1);

final class SecurityHeaders
{
    public static function send(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        $csp = "default-src 'self'; "
             . "base-uri 'self'; "
             . "frame-ancestors 'none'; "
             . "form-action 'self'; "
             . "object-src 'none'";

        header('Content-Security-Policy: ' . $csp);
        header('Cache-Control: no-store');
    }
}