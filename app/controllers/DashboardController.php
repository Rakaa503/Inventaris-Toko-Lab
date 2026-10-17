<?php

declare(strict_types=1);

final class DashboardController
{
    public function index(): void
    {
        require_once __DIR__ . '/../../core/Database.php';

        try {
            $pdo = Database::getConnection();

            $totalItems = (int) $pdo
                ->query('SELECT COUNT(*) FROM items')
                ->fetchColumn();

            $totalCategories = (int) $pdo
                ->query('SELECT COUNT(*) FROM categories')
                ->fetchColumn();

            $lowStock = (int) $pdo
                ->query('SELECT COUNT(*) FROM items WHERE quantity <= minimum_stock')
                ->fetchColumn();

            header('Content-Type: text/html; charset=UTF-8');

            echo '<!doctype html>';
            echo '<html lang="id"><head>';
            echo '<meta charset="UTF-8">';
            echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
            echo '<title>Dashboard - K3 Inventaris</title>';
            echo '<style>
                body{font-family:Arial,sans-serif;max-width:900px;margin:40px auto;padding:0 20px;background:#f4f7fb;color:#172033}
                h1{color:#163b72}
                .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px}
                .card{background:white;padding:24px;border-radius:12px;box-shadow:0 2px 8px #0000000d}
                .number{font-size:32px;font-weight:bold;margin-top:12px}
            </style></head><body>';

            echo '<h1>Sistem Inventaris LAB</h1>';
            echo '<p>Ringkasan inventaris.</p>';
            echo '<div class="cards">';

            echo '<section class="card"><div>Total Barang</div><div class="number">'
                . $totalItems . '</div></section>';

            echo '<section class="card"><div>Total Kategori</div><div class="number">'
                . $totalCategories . '</div></section>';

            echo '<section class="card"><div>Stok Minimum</div><div class="number">'
                . $lowStock . '</div></section>';

            echo '</div></body></html>';
        } catch (Throwable $e) {
            error_log('[DashboardController] ' . $e->getMessage());

            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Terjadi kesalahan saat memuat dashboard.';
        }
    }
}