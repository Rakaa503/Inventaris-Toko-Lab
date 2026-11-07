<?php

declare(strict_types=1);

final class TransactionValidator
{
    public static function validate(array $input): array
    {
        $itemId = filter_var($input['item_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($input['quantity'] ?? null, FILTER_VALIDATE_INT);
        $userIdInput = $input['user_id'] ?? null;
        $userId = $userIdInput === null || $userIdInput === ''
            ? null
            : filter_var($userIdInput, FILTER_VALIDATE_INT);

        $type = strtolower(trim((string) ($input['type'] ?? '')));
        $notes = trim((string) ($input['notes'] ?? ''));

        if ($itemId === false || $itemId < 1) {
            throw new InvalidArgumentException('Barang harus dipilih.');
        }

        if ($quantity === false || $quantity < 1) {
            throw new InvalidArgumentException('Jumlah harus berupa bilangan bulat minimal 1.');
        }

        if (!in_array($type, ['in', 'out'], true)) {
            throw new InvalidArgumentException('Tipe transaksi harus in atau out.');
        }

        if ($userId !== null && ($userId === false || $userId < 1)) {
            throw new InvalidArgumentException('User ID tidak valid.');
        }

        if (strlen($notes) > 500) {
            throw new InvalidArgumentException('Catatan maksimal 500 karakter.');
        }

        return [
            'item_id' => $itemId,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'notes' => $notes === '' ? null : $notes,
        ];
    }
}