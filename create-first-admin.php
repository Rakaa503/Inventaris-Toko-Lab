<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/vendor/autoload.php';

$password = getenv('INVENTORY_ADMIN_PASSWORD') ?: '';
unset($GLOBALS['unused']);

try {
    if (strlen($password) < 8) {
        throw new InvalidArgumentException('Password minimal 8 karakter.');
    }

    $pdo = Database::getConnection();

    $count = (int) $pdo->query(
        'SELECT COUNT(*) FROM users'
    )->fetchColumn();

    if ($count !== 0) {
        throw new RuntimeException(
            'Pembuatan admin pertama dibatalkan: database sudah memiliki user.'
        );
    }

    $name = trim((string) getenv('INVENTORY_ADMIN_NAME'));
    $username = trim((string) getenv('INVENTORY_ADMIN_USERNAME'));
    $email = trim((string) getenv('INVENTORY_ADMIN_EMAIL'));

    $pdo->beginTransaction();

    try {
        $repository = new UserRepository($pdo);

        $id = $repository->create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $pdo->commit();

        echo "ADMIN CREATED SUCCESSFULLY" . PHP_EOL;
        echo "User ID: {$id}" . PHP_EOL;
        echo "Username: {$username}" . PHP_EOL;
        echo "Role: admin" . PHP_EOL;
        echo "Password: tidak ditampilkan" . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
} catch (Throwable $exception) {
    fwrite(STDERR, "FAILED: " . $exception->getMessage() . PHP_EOL);
    exit(1);
} finally {
    unset($password);
}