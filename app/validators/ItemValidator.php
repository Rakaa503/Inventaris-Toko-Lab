<?php

declare(strict_types=1);

final class ItemValidator
{
    public static function validate(array $input): array
    {
        $code = trim((string) ($input['code'] ?? ''));
        $name = trim((string) ($input['name'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $unit = trim((string) ($input['unit'] ?? 'pcs'));
        $stock = filter_var($input['stock'] ?? 0, FILTER_VALIDATE_INT);
        $minimumStock = filter_var(
            $input['minimum_stock'] ?? 0,
            FILTER_VALIDATE_INT
        );
        $price = filter_var($input['price'] ?? 0, FILTER_VALIDATE_FLOAT);
        $categoryId = filter_var(
            $input['category_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($code === '' || strlen($code) > 50) {
            throw new InvalidArgumentException('Kode barang wajib diisi (maksimal 50 karakter).');
        }

        if ($name === '' || strlen($name) > 150) {
            throw new InvalidArgumentException('Nama barang wajib diisi (maksimal 150 karakter).');
        }

        if ($stock === false || $stock < 0) {
            throw new InvalidArgumentException('Stok harus berupa bilangan bulat >= 0.');
        }

        if ($minimumStock === false || $minimumStock < 0) {
            throw new InvalidArgumentException('Minimum stok harus berupa bilangan bulat >= 0.');
        }

        if ($price === false || $price < 0) {
            throw new InvalidArgumentException('Harga harus berupa angka >= 0.');
        }

        if ($unit === '' || strlen($unit) > 30) {
            throw new InvalidArgumentException('Satuan wajib diisi (maksimal 30 karakter).');
        }

        if ($categoryId === false || $categoryId < 1) {
            throw new InvalidArgumentException('Kategori tidak valid.');
        }

        return [
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'stock' => $stock,
            'minimum_stock' => $minimumStock,
            'unit' => $unit,
            'price' => (float) $price,
            'category_id' => $categoryId,
        ];
    }
}