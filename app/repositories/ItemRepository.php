<?php

declare(strict_types=1);

final class ItemRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT
                id,
                category_id,
                item_code,
                name,
                description,
                quantity,
                minimum_stock,
                location,
                condition_status,
                created_at,
                updated_at
             FROM items
             ORDER BY name ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                category_id,
                item_code,
                name,
                description,
                quantity,
                minimum_stock,
                location,
                condition_status,
                created_at,
                updated_at
             FROM items
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $id]);

        $item = $statement->fetch();

        return $item === false ? null : $item;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO items (
                category_id,
                item_code,
                name,
                description,
                quantity,
                minimum_stock,
                location,
                condition_status
             ) VALUES (
                :category_id,
                :item_code,
                :name,
                :description,
                :quantity,
                :minimum_stock,
                :location,
                :condition_status
             )'
        );

        $statement->execute([
            'category_id' => $data['category_id'],
            'item_code' => $data['item_code'],
            'name' => $data['name'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'minimum_stock' => $data['minimum_stock'],
            'location' => $data['location'],
            'condition_status' => $data['condition_status'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE items SET
                category_id = :category_id,
                item_code = :item_code,
                name = :name,
                description = :description,
                quantity = :quantity,
                minimum_stock = :minimum_stock,
                location = :location,
                condition_status = :condition_status
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'category_id' => $data['category_id'],
            'item_code' => $data['item_code'],
            'name' => $data['name'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'minimum_stock' => $data['minimum_stock'],
            'location' => $data['location'],
            'condition_status' => $data['condition_status'],
        ]);

        return $statement->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM items WHERE id = :id'
        );

        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}