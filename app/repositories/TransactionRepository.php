<?php

declare(strict_types=1);

final class TransactionRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function recordStockChange(
        int $itemId,
        ?int $userId,
        string $type,
        int $quantity,
        ?string $notes = null
    ): void {
        if (!$this->pdo->inTransaction()) {
            throw new LogicException('Operasi harus berada dalam transaksi database.');
        }

        $itemQuery = $this->pdo->prepare(
            'SELECT id, stock FROM items WHERE id = :id FOR UPDATE'
        );
        $itemQuery->execute(['id' => $itemId]);
        $item = $itemQuery->fetch();

        if ($item === false) {
            throw new RuntimeException('Barang tidak ditemukan.');
        }

        $currentStock = (int) $item['stock'];

        if ($type === 'in') {
            $newStock = $currentStock + $quantity;
        } elseif ($type === 'out') {
            if ($currentStock < $quantity) {
                throw new DomainException('Stok tidak mencukupi.');
            }
            $newStock = $currentStock - $quantity;
        } else {
            throw new InvalidArgumentException('Tipe transaksi tidak valid.');
        }

        $update = $this->pdo->prepare(
            'UPDATE items SET stock = :stock WHERE id = :id'
        );
        $update->execute(['stock' => $newStock, 'id' => $itemId]);

        $insert = $this->pdo->prepare(
            'INSERT INTO transactions
                (item_id, user_id, type, quantity, notes, created_at)
             VALUES
                (:item_id, :user_id, :type, :quantity, :notes, NOW())'
        );
        $insert->execute([
            'item_id' => $itemId,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'notes' => $notes,
        ]);
    }

    public function findByItemId(int $itemId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, item_id, user_id, type, quantity, notes, created_at
             FROM transactions
             WHERE item_id = :item_id
             ORDER BY created_at DESC, id DESC'
        );
        $statement->execute(['item_id' => $itemId]);

        return $statement->fetchAll();
    }
}