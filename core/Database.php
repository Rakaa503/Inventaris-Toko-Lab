<?php

declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require ROOT_PATH . '/config/database.php';

        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $name = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        if ($driver !== 'mysql') {
            throw new RuntimeException('Driver database tidak didukung.');
        }

        if (!is_string($host) || $host === '') {
            throw new RuntimeException('Host database tidak valid.');
        }

        if (!is_string($port) || !ctype_digit($port)) {
            throw new RuntimeException('Port database tidak valid.');
        }

        if (!is_string($name) || !preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            throw new RuntimeException('Nama database tidak valid.');
        }

        if (!is_string($charset) || !preg_match('/^[a-zA-Z0-9_]+$/', $charset)) {
            throw new RuntimeException('Charset database tidak valid.');
        }

        if (!is_string($username) || !is_string($password)) {
            throw new RuntimeException('Kredensial database tidak valid.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $name,
            $charset
        );

        self::$connection = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]
        );

        return self::$connection;
    }
}