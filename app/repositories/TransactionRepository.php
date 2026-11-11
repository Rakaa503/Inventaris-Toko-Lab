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
            throw new LogicException(
                'Operasi harus berada dalam transaksi database.'
            );
        }

        if ($itemId < 1) {
            throw new InvalidArgumentException('ID barang tidak valid.');
        }

        $allowedTypes = ['stock_in', 'stock_out', 'adjustment'];

        if (!in_array($type, $allowedTypes, true)) {
            throw new InvalidArgumentException('Tipe transaksi tidak valid.');
        }

        if ($quantity < 0 || ($type !== 'adjustment' && $quantity < 1)) {
            throw new InvalidArgumentException('Jumlah transaksi tidak valid.');
        }

        if ($notes !== null && strlen($notes) > 500) {
            throw new InvalidArgumentException(
                'Catatan maksimal 500 karakter.'
            );
        }

        $itemQuery = $this->pdo->prepare(
            'SELECT id, quantity
             FROM items
             WHERE id = :id
             FOR UPDATE'
        );

        $itemQuery->execute(['id' => $itemId]);

        $item = $itemQuery->fetch();

        if ($item === false) {
            throw new RuntimeException('Barang tidak ditemukan.');
        }

        $currentStock = (int) $item['quantity'];

        if ($type === 'stock_in') {
            $newStock = $currentStock + $quantity;
        } elseif ($type === 'stock_out') {
            if ($currentStock < $quantity) {
                throw new DomainException('Stok tidak mencukupi.');
            }

            $newStock = $currentStock - $quantity;
        } else {
            $newStock = $quantity;
        }

        if ($newStock > 4294967295) {
            throw new OverflowException(
                'Jumlah stok melebihi batas database.'
            );
        }

        $update = $this->pdo->prepare(
            'UPDATE items
             SET quantity = :quantity
             WHERE id = :id'
        );

        $update->execute([
            'quantity' => $newStock,
            'id' => $itemId,
        ]);

        $insert = $this->pdo->prepare(
            'INSERT INTO transactions (
                item_id,
                user_id,
                transaction_type,
                quantity,
                notes,
                created_at
             ) VALUES (
                :item_id,
                :user_id,
                :transaction_type,
                :quantity,
                :notes,
                NOW()
             )'
        );

        $insert->execute([
            'item_id' => $itemId,
            'user_id' => $userId,
            'transaction_type' => $type,
            'quantity' => $quantity,
            'notes' => $notes,
        ]);
    }

    public function findByItemId(int $itemId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                item_id,
                user_id,
                transaction_type,
                quantity,
                notes,
                created_at
             FROM transactions
             WHERE item_id = :item_id
             ORDER BY created_at DESC, id DESC'
        );

        $statement->execute(['item_id' => $itemId]);

        return $statement->fetchAll();
    }
}