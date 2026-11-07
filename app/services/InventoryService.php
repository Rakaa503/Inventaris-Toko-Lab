<?php

declare(strict_types=1);

final class InventoryService
{
    public function __construct(
        private PDO $pdo,
        private TransactionRepository $transactions
    ) {
    }

    public function changeStock(
        int $itemId,
        ?int $userId,
        string $type,
        int $quantity,
        ?string $notes = null
    ): void {
        if ($itemId < 1) {
            throw new InvalidArgumentException('ID barang tidak valid.');
        }

        if ($quantity < 1) {
            throw new InvalidArgumentException('Jumlah transaksi minimal 1.');
        }

        if (!in_array($type, ['in', 'out'], true)) {
            throw new InvalidArgumentException('Tipe transaksi harus in atau out.');
        }

        if ($this->pdo->inTransaction()) {
            throw new LogicException('Koneksi sedang memiliki transaksi aktif.');
        }

        $this->pdo->beginTransaction();

        try {
            $this->transactions->recordStockChange(
                $itemId,
                $userId,
                $type,
                $quantity,
                $notes
            );

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}