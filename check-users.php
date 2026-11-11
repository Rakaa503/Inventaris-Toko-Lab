<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/vendor/autoload.php';

try {
    $pdo = Database::getConnection();

    $count = (int) $pdo->query(
        'SELECT COUNT(*) FROM users'
    )->fetchColumn();

    echo "Database: inventaris_k3" . PHP_EOL;
    echo "Total user: {$count}" . PHP_EOL;

    if ($count === 0) {
        echo "Status: Belum ada akun pengguna." . PHP_EOL;
    } else {
        echo "Status: Sudah ada akun pengguna." . PHP_EOL;
    }
} catch (Throwable $exception) {
    echo "Pemeriksaan gagal: " . $exception->getMessage() . PHP_EOL;
    exit(1);
}