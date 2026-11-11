<?php

declare(strict_types=1);

final class Category
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {
        if ($this->id < 1) {
            throw new InvalidArgumentException(
                'ID kategori tidak valid.'
            );
        }

        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'Nama kategori tidak boleh kosong.'
            );
        }
    }

    public static function fromArray(array $data): self
    {
        foreach (['id', 'name'] as $field) {
            if (!array_key_exists($field, $data)) {
                throw new UnexpectedValueException(
                    "Kolom kategori tidak ditemukan: {$field}"
                );
            }
        }

        if (
            !is_numeric($data['id'])
            || !is_string($data['name'])
        ) {
            throw new UnexpectedValueException(
                'Format data kategori tidak valid.'
            );
        }

        foreach (['description', 'created_at', 'updated_at'] as $field) {
            if (
                isset($data[$field])
                && !is_string($data[$field])
            ) {
                throw new UnexpectedValueException(
                    "Kolom {$field} harus berupa string atau null."
                );
            }
        }

        return new self(
            id: (int) $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}