<?php

declare(strict_types=1);

$conditionLabels = [
    'baik' => 'Baik',
    'rusak_ringan' => 'Rusak ringan',
    'rusak_berat' => 'Rusak berat',
];

$transactionLabels = [
    'stock_in' => 'Barang masuk',
    'stock_out' => 'Barang keluar',
    'adjustment' => 'Penyesuaian',
];

$queryParameters = [
    'q' => $search,
    'category' => $categoryId,
    'condition' => $condition,
];

$queryParameters = array_filter(
    $queryParameters,
    static fn ($value): bool => $value !== null && $value !== ''
);

$selectedLow = $selectedItem !== null
    && (int) $selectedItem['quantity'] <= (int) $selectedItem['minimum_stock'];

$selectedQuantity = (int) ($selectedItem['quantity'] ?? 0);
$selectedMinimum = (int) ($selectedItem['minimum_stock'] ?? 0);

$stockPercent = $selectedMinimum > 0
    ? min(100, (int) round(($selectedQuantity / $selectedMinimum) * 100))
    : ($selectedQuantity > 0 ? 100 : 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#191b1f">
    <title>LabStock — Inventaris Laboratorium</title>
    <link rel="stylesheet" href="<?= e(url('css/dashboard.css')) ?>">
    <script src="<?= e(url('js/theme.js')) ?>" defer></script>
</head>
<body>
<div class="desktop">

    <header class="topbar">
        <a class="brand" href="<?= e(url()) ?>">
            <span class="brand-copy">
                <strong>LabStock</strong>
                <small>INVENTARIS LABORATORIUM</small>
            </span>
        </a>

        <nav class="top-nav" aria-label="Navigasi utama">
            <a class="nav-link active" href="<?= e(url()) ?>">Inventaris</a>
        </nav>

        <button
            class="theme-toggle"
            id="theme-toggle"
            type="button"
            aria-label="Aktifkan dark mode"
            aria-pressed="false"
        >
            <span class="theme-toggle-icon" aria-hidden="true">◐</span>
            <span class="theme-toggle-label">Mode gelap</span>
        </button>
        <div class="account">
            <div class="account-info">
                <strong><?= e($user->name) ?></strong>
                <small><?= e(strtoupper($user->role)) ?></small>
            </div>
            <span class="avatar"><?= e(strtoupper(substr($user->name, 0, 1))) ?></span>
            <form action="<?= e(url('logout')) ?>" method="POST">
                <?= Csrf::field() ?>
                <button class="logout-button" type="submit">Keluar</button>
            </form>
        </div>
    </header>

    <main class="app-panel">

        <section class="page-heading">
            <div>
                <div class="eyebrow">PENGELOLAAN / INVENTARIS</div>
                <h1>Inventaris Laboratorium</h1>
                <p>Daftar barang, kondisi, dan ketersediaan stok.</p>
            </div>
            <div class="date-label">
                <span class="date-caption">TANGGAL SISTEM</span>
                <strong><?= e(date('d M Y')) ?></strong>
            </div>
        </section>

        <section class="summary-strip" aria-label="Ringkasan inventaris">
            <div class="summary-item">
                <span>Total barang</span>
                <strong><?= number_format($totalItems) ?></strong>
            </div>
            <div class="summary-item">
                <span>Kategori</span>
                <strong><?= number_format($totalCategories) ?></strong>
            </div>
            <div class="summary-item summary-warning">
                <span>Stok minimum</span>
                <strong><?= number_format($lowStock) ?></strong>
            </div>
            <div class="summary-item summary-danger">
                <span>Barang rusak</span>
                <strong><?= number_format($damagedItems) ?></strong>
            </div>
            <div class="summary-item">
                <span>Total transaksi</span>
                <strong><?= number_format($totalTransactions) ?></strong>
            </div>
        </section>

        <section class="workspace-grid">

            <section class="inventory-panel">
                <div class="inventory-heading">
                    <div>
                        <div class="eyebrow">DATA MASTER</div>
                        <h2>Daftar barang</h2>
                    </div>
                    <span class="record-count">
                        <?= number_format($filteredItems) ?> data
                    </span>
                </div>

                <form class="filter-bar" method="GET" action="<?= e(url()) ?>">
                    <label class="search-field">
                        <span class="visually-hidden">Cari barang</span>
                        <input
                            type="search"
                            name="q"
                            value="<?= e($search) ?>"
                            placeholder="Cari nama atau kode barang..."
                        >
                    </label>

                    <label>
                        <span class="visually-hidden">Kategori</span>
                        <select name="category">
                            <option value="">Semua kategori</option>
                            <?php foreach ($categories as $categoryOption): ?>
                                <option
                                    value="<?= (int) $categoryOption['id'] ?>"
                                    <?= $categoryId === (int) $categoryOption['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($categoryOption['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span class="visually-hidden">Kondisi</span>
                        <select name="condition">
                            <option value="">Semua kondisi</option>
                            <?php foreach ($conditionLabels as $key => $label): ?>
                                <option
                                    value="<?= e($key) ?>"
                                    <?= $condition === $key ? 'selected' : '' ?>
                                >
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <button class="filter-submit" type="submit">Terapkan</button>
                    <a class="reset-link" href="<?= e(url()) ?>">Reset</a>
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="number-column">No.</th>
                                <th>Barang</th>
                                <th>Kategori</th>
                                <th>Lokasi</th>
                                <th>Jumlah</th>
                                <th>Kondisi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($items === []): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <strong>
                                            <?= $filteredItems === 0 && $search === '' && $categoryId === null && $condition === ''
                                                ? 'Belum ada barang terdaftar'
                                                : 'Barang tidak ditemukan' ?>
                                        </strong>
                                        <span>
                                            <?= $filteredItems === 0 && $search === '' && $categoryId === null && $condition === ''
                                                ? 'Data barang akan ditampilkan setelah tersedia di database.'
                                                : 'Coba ubah kata pencarian atau filter.' ?>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $index => $item): ?>
                                <?php
                                $isLow = (int) $item['quantity'] <= (int) $item['minimum_stock'];
                                $isSelected = $selectedItem !== null
                                    && (int) $selectedItem['id'] === (int) $item['id'];
                                $itemUrl = url() . '?' . http_build_query(
                                    array_merge($queryParameters, ['item' => (int) $item['id'], 'page' => $page])
                                );
                                ?>
                                <tr class="<?= $isSelected ? 'selected-row' : '' ?>">
                                    <td class="number-column"><?= $offset + $index + 1 ?></td>
                                    <td>
                                        <a class="item-link" href="<?= e($itemUrl) ?>">
                                            <strong><?= e($item['name']) ?></strong>
                                            <span><?= e($item['item_code']) ?></span>
                                        </a>
                                    </td>
                                    <td><?= e($item['category_name'] ?? 'Tanpa kategori') ?></td>
                                    <td><?= e($item['location'] ?: '—') ?></td>
                                    <td>
                                        <strong class="<?= $isLow ? 'stock-low' : 'stock-normal' ?>">
                                            <?= number_format((int) $item['quantity']) ?>
                                        </strong>
                                        <span class="stock-min">Min. <?= number_format((int) $item['minimum_stock']) ?></span>
                                    </td>
                                    <td>
                                        <span class="condition condition-<?= e($item['condition_status']) ?>">
                                            <?= e($conditionLabels[$item['condition_status']] ?? $item['condition_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="inspect-link" href="<?= e($itemUrl) ?>" aria-label="Lihat detail <?= e($item['name']) ?>">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <span>
                        Menampilkan <?= count($items) ?> dari <?= number_format($filteredItems) ?> data
                    </span>

                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?= e(url() . '?' . http_build_query(array_merge($queryParameters, ['page' => $page - 1, 'item' => $selectedItem['id'] ?? null]))) ?>">Sebelumnya</a>
                        <?php else: ?>
                            <span class="page-disabled">Sebelumnya</span>
                        <?php endif; ?>

                        <strong><?= $page ?> / <?= $totalPages ?></strong>

                        <?php if ($page < $totalPages): ?>
                            <a href="<?= e(url() . '?' . http_build_query(array_merge($queryParameters, ['page' => $page + 1, 'item' => $selectedItem['id'] ?? null]))) ?>">Berikutnya</a>
                        <?php else: ?>
                            <span class="page-disabled">Berikutnya</span>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <aside class="inspector-panel">
                <div class="inspector-heading">
                    <div>
                        <div class="eyebrow">DETAIL DATA</div>
                        <h2>Informasi barang</h2>
                    </div>
                    <?php if ($selectedItem !== null): ?>
                        <span class="record-count">#<?= (int) $selectedItem['id'] ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($selectedItem === null): ?>
                    <div class="inspector-empty">
                        <strong>Belum ada barang dipilih</strong>
                        <span>Pilih salah satu baris pada daftar untuk melihat detailnya.</span>
                    </div>
                <?php else: ?>
                    <div class="inspector-content">
                        <span class="inspector-code"><?= e($selectedItem['item_code']) ?></span>
                        <h3><?= e($selectedItem['name']) ?></h3>

                        <span class="condition condition-<?= e($selectedItem['condition_status']) ?>">
                            <?= e($conditionLabels[$selectedItem['condition_status']] ?? $selectedItem['condition_status']) ?>
                        </span>

                        <div class="detail-list">
                            <div>
                                <span>Kategori</span>
                                <strong><?= e($selectedItem['category_name'] ?? 'Tanpa kategori') ?></strong>
                            </div>
                            <div>
                                <span>Lokasi</span>
                                <strong><?= e($selectedItem['location'] ?: '—') ?></strong>
                            </div>
                            <div>
                                <span>Jumlah stok</span>
                                <strong><?= number_format($selectedQuantity) ?></strong>
                            </div>
                            <div>
                                <span>Stok minimum</span>
                                <strong><?= number_format($selectedMinimum) ?></strong>
                            </div>
                        </div>

                        <div class="stock-inspector">
                            <div class="stock-inspector-heading">
                                <span>Perbandingan stok</span>
                                <strong><?= $stockPercent ?>%</strong>
                            </div>
                            <progress
                                class="stock-progress <?= $selectedLow ? 'progress-warning' : '' ?>"
                                value="<?= $stockPercent ?>"
                                max="100"
                            ><?= $stockPercent ?>%</progress>
                            <p>
                                <?= $selectedLow
                                    ? 'Stok berada pada atau di bawah batas minimum.'
                                    : 'Stok berada di atas batas minimum.' ?>
                            </p>
                        </div>

                        <?php if (!empty($selectedItem['description'])): ?>
                            <div class="description-block">
                                <span>Deskripsi</span>
                                <p><?= nl2br(e($selectedItem['description'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="record-dates">
                            <span>Dibuat: <?= e(date('d/m/Y', strtotime($selectedItem['created_at']))) ?></span>
                            <span>Diperbarui: <?= e(date('d/m/Y', strtotime($selectedItem['updated_at']))) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </section>

        <section class="activity-panel">
            <div class="activity-heading">
                <div>
                    <div class="eyebrow">RIWAYAT PERGERAKAN</div>
                    <h2>Transaksi terbaru</h2>
                </div>
                <span class="record-count">5 terakhir</span>
            </div>

            <?php if ($transactions === []): ?>
                <p class="activity-empty">Belum ada transaksi yang tercatat.</p>
            <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($transactions as $transaction): ?>
                        <div class="activity-row">
                            <div class="activity-description">
                                <strong><?= e($transaction['item_name'] ?? 'Barang tidak tersedia') ?></strong>
                                <span>
                                    <?= e($transactionLabels[$transaction['transaction_type']] ?? $transaction['transaction_type']) ?>
                                    · <?= e($transaction['item_code'] ?? '—') ?>
                                </span>
                            </div>
                            <div class="activity-meta">
                                <strong><?= number_format((int) $transaction['quantity']) ?> unit</strong>
                                <span><?= e($transaction['user_name'] ?? 'Pengguna tidak tersedia') ?></span>
                            </div>
                            <time><?= e(date('d/m/Y H:i', strtotime($transaction['created_at']))) ?></time>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <footer class="app-footer">
            <span>LabStock · Inventaris Laboratorium</span>
            <span>AVILLA.DEV · <?= date('Y') ?></span>
        </footer>

    </main>
</div>
</body>
</html>