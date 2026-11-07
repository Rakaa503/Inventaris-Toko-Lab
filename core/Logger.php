<?php

declare(strict_types=1);
final class Logger
{
    public static function info(string $message, array $context = []): void
    {
    self::write('INFO', $message, $context);
    }
    public static function warning(string $message, array $context = []): void
    {
    self::write('WARNING', $message, $context);
    }
    public static function error(string $message, array $context = []): void
    {
    self::write('ERROR', $message, $context);
    }
    private static function write(
    string $level,
    string $message,
    array $context
    ): void {
    $directory = ROOT_PATH . '/storage/logs';
    if (!is_dir($directory)
    && !mkdir($directory, 0750, true)
    && !is_dir($directory)
    ) {
    error_log('Logger: tidak dapat membuat direktori log.');
    return;
    }
    $safeContext = self::sanitize($context);
    $entry = sprintf(
    "[%s] %s: %s %s%s",
    date(DATE_ATOM),
    $level,
    $message,
    json_encode($safeContext, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR),
    PHP_EOL
    );
    $written = file_put_contents(
    $directory . '/app.log',
    $entry,
    FILE_APPEND | LOCK_EX
    );
    if ($written === false) {
    error_log('Logger: gagal menulis log.');
    }
    }
    private static function sanitize(array $context): array
    {
    $blocked = ['password', 'token', 'authorization', 'cookie', 'secret'];
    foreach ($context as $key => $value) {
    if (in_array(strtolower((string) $key), $blocked, true)) {
    $context[$key] = '[REDACTED]';
 } elseif (is_array($value)) {
    $context[$key] = self::sanitize($value);
 }
 }
        return $context;
    }
}
