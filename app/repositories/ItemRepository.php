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
            'SELECT id, category_id, code, name, description, stock,
                    minimum_stock, unit, price
             FROM items
             ORDER BY name ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, category_id, code, name, description, stock,
                    minimum_stock, unit, price
             FROM items WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $item = $statement->fetch();

        return $item === false ? null : $item;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO items
                (category_id, code, name, description, stock,
                 minimum_stock, unit, price)
             VALUES
                (:category_id, :code, :name, :description, :stock,
                 :minimum_stock, :unit, :price)'
        );

        $statement->execute([
            'category_id' => $data['category_id'],
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'],
            'stock' => $data['stock'],
            'minimum_stock' => $data['minimum_stock'],
            'unit' => $data['unit'],
            'price' => $data['price'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE items SET
                category_id = :category_id,
                code = :code,
                name = :name,
                description = :description,
                stock = :stock,
                minimum_stock = :minimum_stock,
                unit = :unit,
                price = :price
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'category_id' => $data['category_id'],
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'],
            'stock' => $data['stock'],
            'minimum_stock' => $data['minimum_stock'],
            'unit' => $data['unit'],
            'price' => $data['price'],
        ]);

        return $statement->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM items WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}