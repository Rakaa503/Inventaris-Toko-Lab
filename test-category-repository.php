<?php

declare(strict_types=1);

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/core/Env.php';
Env::load(ROOT_PATH . '/.env');

require_once ROOT_PATH . '/app/models/Category.php';
require_once ROOT_PATH . '/app/repositories/CategoryRepository.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException("FAILED: {$message}");
    }

    echo "[PASS] {$message}" . PHP_EOL;
}

$config = require ROOT_PATH . '/config/database.php';
$config['database'] = 'inventaris_k3_test';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
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
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]
);

echo "========== CATEGORY REPOSITORY TEST ==========" . PHP_EOL;
echo "Database: inventaris_k3_test" . PHP_EOL;

$repository = new CategoryRepository($pdo);
$exitCode = 0;

try {
    $pdo->beginTransaction();

    $uniqueName = 'Category Test ' . bin2hex(random_bytes(4));

    $id = $repository->create([
        'name' => $uniqueName,
        'description' => 'Deskripsi kategori pengujian',
    ]);

    check($id > 0, 'Create category');

    $category = $repository->findById($id);

    check($category instanceof Category, 'Find category by ID');
    check($category->id === $id, 'Category ID mapping');
    check($category->name === $uniqueName, 'Category name mapping');
    check(
        $category->description === 'Deskripsi kategori pengujian',
        'Category description mapping'
    );

    $foundByName = $repository->findByName($uniqueName);

    check(
        $foundByName instanceof Category
        && $foundByName->id === $id,
        'Find category by name'
    );

    check(
        $repository->findByName('   ') === null,
        'Empty category name search'
    );

    $allCategories = $repository->findAll();

    $foundInList = false;

    foreach ($allCategories as $item) {
        if ($item instanceof Category && $item->id === $id) {
            $foundInList = true;
            break;
        }
    }

    check($foundInList, 'Find all categories');

    $updated = $repository->update($id, [
        'name' => $uniqueName . ' Updated',
        'description' => 'Deskripsi setelah diperbarui',
    ]);

    check($updated, 'Update category');

    $updatedCategory = $repository->findById($id);

    check(
        $updatedCategory?->name === $uniqueName . ' Updated',
        'Updated category name'
    );

    check(
        $updatedCategory?->description === 'Deskripsi setelah diperbarui',
        'Updated category description'
    );

    $deleted = $repository->delete($id);

    check($deleted, 'Delete category');
    check(
        $repository->findById($id) === null,
        'Deleted category cannot be found'
    );
    check(
        !$repository->delete($id),
        'Delete missing category'
    );

    check(
        $repository->findById(0) === null,
        'Invalid ID search handling'
    );

    try {
        $repository->create([
            'name' => '   ',
            'description' => null,
        ]);

        throw new RuntimeException('Empty category name was accepted.');
    } catch (InvalidArgumentException) {
        echo "[PASS] Empty category name rejection" . PHP_EOL;
    }

    try {
        $repository->create([
            'name' => 'Invalid Description Test',
            'description' => [],
        ]);

        throw new RuntimeException('Invalid description was accepted.');
    } catch (InvalidArgumentException) {
        echo "[PASS] Invalid description rejection" . PHP_EOL;
    }

    try {
        $repository->update(0, [
            'name' => 'Invalid ID',
            'description' => null,
        ]);

        throw new RuntimeException('Invalid update ID was accepted.');
    } catch (InvalidArgumentException) {
        echo "[PASS] Invalid update ID rejection" . PHP_EOL;
    }

    echo PHP_EOL . "ALL CATEGORY REPOSITORY TESTS PASSED" . PHP_EOL;
} catch (Throwable $exception) {
    $exitCode = 1;

    fwrite(
        STDERR,
        PHP_EOL . "[FAIL] " . $exception->getMessage() . PHP_EOL
    );
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
        echo "[PASS] Test transaction rolled back" . PHP_EOL;
    }
}

exit($exitCode);