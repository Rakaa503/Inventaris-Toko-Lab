<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

$config = require __DIR__ . '/config/database.php';
$config['database'] = 'inventaris_k3_test';

$dsn = sprintf(
    '%s:host=%s;port=%s;dbname=%s;charset=%s',
    $config['driver'],
    $config['host'],
    $config['port'],
    $config['database'],
    $config['charset']
);

try {
    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    echo "Database: inventaris_k3_test" . PHP_EOL;
    echo "Connection: SUCCESS" . PHP_EOL;

    $tables = $pdo->query('SHOW TABLES')->fetchAll();

    echo "Tables found: " . count($tables) . PHP_EOL;
} catch (Throwable $exception) {
    echo "Connection: FAILED" . PHP_EOL;
    echo "Error: " . $exception->getMessage() . PHP_EOL;
    exit(1);
}