<?php

declare(strict_types=1);

final class DashboardController extends Controller
{
    private const ITEMS_PER_PAGE = 15;

    public function index(User $user): void
    {
        try {
            $pdo = Database::getConnection();

            $totalItems = (int) $pdo
                ->query('SELECT COUNT(*) FROM items')
                ->fetchColumn();

            $totalCategories = (int) $pdo
                ->query('SELECT COUNT(*) FROM categories')
                ->fetchColumn();

            $lowStock = (int) $pdo
                ->query(
                    'SELECT COUNT(*)
                     FROM items
                     WHERE quantity <= minimum_stock'
                )
                ->fetchColumn();

            $damagedItems = (int) $pdo
                ->query(
                    "SELECT COUNT(*)
                     FROM items
                     WHERE condition_status IN ('rusak_ringan', 'rusak_berat')"
                )
                ->fetchColumn();

            $totalTransactions = (int) $pdo
                ->query('SELECT COUNT(*) FROM transactions')
                ->fetchColumn();

            $categories = $pdo
                ->query('SELECT id, name FROM categories ORDER BY name ASC')
                ->fetchAll(PDO::FETCH_ASSOC);

            $search = trim((string) ($_GET['q'] ?? ''));
            $categoryId = filter_var(
                $_GET['category'] ?? null,
                FILTER_VALIDATE_INT
            );
            $condition = (string) ($_GET['condition'] ?? '');

            $allowedConditions = [
                'baik',
                'rusak_ringan',
                'rusak_berat',
            ];

            if (!in_array($condition, $allowedConditions, true)) {
                $condition = '';
            }

            if ($categoryId === false || $categoryId < 1) {
                $categoryId = null;
            }

            $where = [];
            $parameters = [];

            if ($search !== '') {
                $where[] = '(i.name LIKE :search_name OR i.item_code LIKE :search_code)';
                $parameters['search_name'] = '%' . $search . '%';
                $parameters['search_code'] = '%' . $search . '%';
            }

            if ($categoryId !== null) {
                $where[] = 'i.category_id = :category_id';
                $parameters['category_id'] = $categoryId;
            }

            if ($condition !== '') {
                $where[] = 'i.condition_status = :condition_status';
                $parameters['condition_status'] = $condition;
            }

            $whereSql = $where === []
                ? ''
                : 'WHERE ' . implode(' AND ', $where);

            $countStatement = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM items i
                 $whereSql"
            );

            foreach ($parameters as $key => $value) {
                $countStatement->bindValue(':' . $key, $value);
            }

            $countStatement->execute();
            $filteredItems = (int) $countStatement->fetchColumn();

            $totalPages = max(
                1,
                (int) ceil($filteredItems / self::ITEMS_PER_PAGE)
            );

            $page = filter_var(
                $_GET['page'] ?? 1,
                FILTER_VALIDATE_INT
            );

            $page = $page === false ? 1 : max(1, min($page, $totalPages));

            $offset = ($page - 1) * self::ITEMS_PER_PAGE;

            $itemsStatement = $pdo->prepare(
                "SELECT
                    i.id,
                    i.item_code,
                    i.name,
                    i.quantity,
                    i.minimum_stock,
                    i.location,
                    i.condition_status,
                    c.name AS category_name
                 FROM items i
                 LEFT JOIN categories c ON c.id = i.category_id
                 $whereSql
                 ORDER BY
                    CASE WHEN i.quantity <= i.minimum_stock THEN 0 ELSE 1 END,
                    i.name ASC
                 LIMIT :limit OFFSET :offset"
            );

            foreach ($parameters as $key => $value) {
                $itemsStatement->bindValue(':' . $key, $value);
            }

            $itemsStatement->bindValue(
                ':limit',
                self::ITEMS_PER_PAGE,
                PDO::PARAM_INT
            );

            $itemsStatement->bindValue(
                ':offset',
                $offset,
                PDO::PARAM_INT
            );

            $itemsStatement->execute();
            $items = $itemsStatement->fetchAll(PDO::FETCH_ASSOC);

            $selectedId = filter_var(
                $_GET['item'] ?? null,
                FILTER_VALIDATE_INT
            );

            $selectedItem = null;

            if ($selectedId !== false && $selectedId !== null && $selectedId > 0) {
                $selectedStatement = $pdo->prepare(
                    'SELECT
                        i.id,
                        i.item_code,
                        i.name,
                        i.description,
                        i.quantity,
                        i.minimum_stock,
                        i.location,
                        i.condition_status,
                        i.created_at,
                        i.updated_at,
                        c.name AS category_name
                     FROM items i
                     LEFT JOIN categories c ON c.id = i.category_id
                     WHERE i.id = :id
                     LIMIT 1'
                );

                $selectedStatement->execute(['id' => $selectedId]);
                $selectedItem = $selectedStatement->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            $transactions = $pdo->query(
                'SELECT
                    t.id,
                    t.transaction_type,
                    t.quantity,
                    t.notes,
                    t.created_at,
                    i.name AS item_name,
                    i.item_code,
                    u.name AS user_name
                 FROM transactions t
                 LEFT JOIN items i ON i.id = t.item_id
                 LEFT JOIN users u ON u.id = t.user_id
                 ORDER BY t.created_at DESC, t.id DESC
                 LIMIT 5'
            )->fetchAll(PDO::FETCH_ASSOC);

            $this->render('dashboard/index', [
                'user' => $user,
                'totalItems' => $totalItems,
                'totalCategories' => $totalCategories,
                'lowStock' => $lowStock,
                'damagedItems' => $damagedItems,
                'totalTransactions' => $totalTransactions,
                'items' => $items,
                'categories' => $categories,
                'transactions' => $transactions,
                'selectedItem' => $selectedItem,
                'search' => $search,
                'categoryId' => $categoryId,
                'condition' => $condition,
                'filteredItems' => $filteredItems,
                'page' => $page,
                'totalPages' => $totalPages,
                'itemsPerPage' => self::ITEMS_PER_PAGE,
            ]);
        } catch (Throwable $exception) {
            error_log('[DashboardController] ' . $exception->getMessage());

            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Terjadi kesalahan saat memuat dashboard.';
        }
    }
}