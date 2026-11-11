<?php

declare(strict_types=1);

ob_start();

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/core/Env.php';
require_once ROOT_PATH . '/core/Csrf.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/repositories/UserRepository.php';
require_once ROOT_PATH . '/app/services/AuthService.php';

$passed = 0;
$failed = 0;
$pdo = null;

$check = static function (string $label, bool $condition) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        echo "[PASS] {$label}" . PHP_EOL;
        return;
    }

    $failed++;
    echo "[FAIL] {$label}" . PHP_EOL;
};

try {
    Env::load(ROOT_PATH . '/.env');

    $config = require ROOT_PATH . '/config/database.php';
    $config['database'] = 'inventaris_k3_test';

    $dsn = sprintf(
        '%s:host=%s;port=%s;dbname=%s;charset=%s',
        $config['driver'],
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );

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

    $pdo->beginTransaction();

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_save_path(sys_get_temp_dir());
        session_id('auth-test-' . bin2hex(random_bytes(8)));

        if (!session_start()) {
            throw new RuntimeException('Gagal memulai session pengujian.');
        }
    }

    $users = new UserRepository($pdo);
    $auth = new AuthService($users);

    $suffix = bin2hex(random_bytes(5));
    $password = 'TestPassword#2026';

    $activeId = $users->create([
        'name' => 'Auth Test Active',
        'username' => 'auth_active_' . $suffix,
        'email' => 'auth_active_' . $suffix . '@example.test',
        'password' => $password,
        'role' => 'admin',
        'is_active' => true,
    ]);

    $inactiveId = $users->create([
        'name' => 'Auth Test Inactive',
        'username' => 'auth_inactive_' . $suffix,
        'email' => 'auth_inactive_' . $suffix . '@example.test',
        'password' => $password,
        'role' => 'viewer',
        'is_active' => false,
    ]);

    $activeUser = $users->findById($activeId);
    $inactiveUser = $users->findById($inactiveId);

    $check('Active test account created', $activeUser !== null);
    $check('Inactive test account created', $inactiveUser !== null);
    $check('Inactive account status mapped correctly', $inactiveUser !== null && !$inactiveUser->isActive);

    $oldSessionId = session_id();

    $loginByUsername = $auth->login(
        'auth_active_' . $suffix,
        $password
    );

    $check('Login using username', $loginByUsername !== null);
    $check('Session stores authenticated user ID', ($_SESSION['user_id'] ?? null) === $activeId);
    $check('Session ID regenerated after login', session_id() !== $oldSessionId);
    $check('Authentication timestamp stored', isset($_SESSION['authenticated_at']));
    $check('CSRF token generated after login', isset($_SESSION['_csrf_token']) && is_string($_SESSION['_csrf_token']));

    $currentUser = $auth->currentUser();

    $check('Current user loaded from database', $currentUser !== null && $currentUser->id === $activeId);
    $check('isAuthenticated returns true after login', $auth->isAuthenticated());

    $auth->logout();

    $check('Logout removes user session', !isset($_SESSION['user_id']));
    $check('Logout removes authentication timestamp', !isset($_SESSION['authenticated_at']));
    $check('isAuthenticated returns false after logout', !$auth->isAuthenticated());

    $loginByEmail = $auth->login(
        'auth_active_' . $suffix . '@example.test',
        $password
    );

    $check('Login using email', $loginByEmail !== null && $loginByEmail->id === $activeId);

    $auth->logout();

    $check(
        'Wrong password rejected',
        $auth->login('auth_active_' . $suffix, 'WrongPassword#2026') === null
    );

    $check(
        'Blank identifier rejected',
        $auth->login('', $password) === null
    );

    $check(
        'Unknown account rejected',
        $auth->login('missing_' . $suffix, $password) === null
    );

    $check(
        'Inactive account rejected',
        $auth->login('auth_inactive_' . $suffix, $password) === null
    );

    $check(
        'Failed login does not authenticate session',
        !isset($_SESSION['user_id'])
    );

} catch (Throwable $exception) {
    $failed++;
    echo '[ERROR] ' . $exception->getMessage() . PHP_EOL;
} finally {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_destroy();
    }
}

echo PHP_EOL;
echo "========== AUTH SERVICE TEST SUMMARY ==========" . PHP_EOL;
echo "Passed: {$passed}" . PHP_EOL;
echo "Failed: {$failed}" . PHP_EOL;
echo "Database: inventaris_k3_test" . PHP_EOL;
echo "Transaction: ROLLBACK" . PHP_EOL;

$output = ob_get_clean();
echo $output;

if ($failed > 0) {
    exit(1);
}