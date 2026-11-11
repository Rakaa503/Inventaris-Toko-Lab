<?php

declare(strict_types=1);

final class CategoryRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT
                id,
                name,
                description,
                created_at,
                updated_at
             FROM categories
             ORDER BY name ASC'
        );

        $categories = [];

        foreach ($statement->fetchAll() as $category) {
            $categories[] = Category::fromArray($category);
        }

        return $categories;
    }

    public function findById(int $id): ?Category
    {
        if ($id < 1) {
            return null;
        }

        $statement = $this->pdo->prepare(
            'SELECT
                id,
                name,
                description,
                created_at,
                updated_at
             FROM categories
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $id]);

        $category = $statement->fetch();

        return $category === false
            ? null
            : Category::fromArray($category);
    }

    public function findByName(string $name): ?Category
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        $statement = $this->pdo->prepare(
            'SELECT
                id,
                name,
                description,
                created_at,
                updated_at
             FROM categories
             WHERE name = :name
             LIMIT 1'
        );

        $statement->execute(['name' => $name]);

        $category = $statement->fetch();

        return $category === false
            ? null
            : Category::fromArray($category);
    }

    public function create(array $data): int
    {
        $name = $this->validateName($data['name'] ?? null);
        $description = $this->validateDescription(
            $data['description'] ?? null
        );

        $statement = $this->pdo->prepare(
            'INSERT INTO categories (
                name,
                description
             ) VALUES (
                :name,
                :description
             )'
        );

        $statement->execute([
            'name' => $name,
            'description' => $description,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        if ($id < 1) {
            throw new InvalidArgumentException(
                'ID kategori tidak valid.'
            );
        }

        $name = $this->validateName($data['name'] ?? null);
        $description = $this->validateDescription(
            $data['description'] ?? null
        );

        $statement = $this->pdo->prepare(
            'UPDATE categories SET
                name = :name,
                description = :description
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'name' => $name,
            'description' => $description,
        ]);

        return $statement->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        if ($id < 1) {
            throw new InvalidArgumentException(
                'ID kategori tidak valid.'
            );
        }

        $statement = $this->pdo->prepare(
            'DELETE FROM categories
             WHERE id = :id'
        );

        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function validateName(mixed $name): string
    {
        if (
            !is_string($name)
            || trim($name) === ''
        ) {
            throw new InvalidArgumentException(
                'Nama kategori tidak boleh kosong.'
            );
        }

        return trim($name);
    }

    private function validateDescription(mixed $description): ?string
    {
        if ($description === null) {
            return null;
        }

        if (!is_string($description)) {
            throw new InvalidArgumentException(
                'Deskripsi kategori harus berupa teks.'
            );
        }

        $description = trim($description);

        return $description === '' ? null : $description;
    }
}