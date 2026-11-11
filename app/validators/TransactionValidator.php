<?php

declare(strict_types=1);

final class TransactionValidator
{
    public static function validate(array $input): array
    {
        $itemId = filter_var(
            $input['item_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $quantity = filter_var(
            $input['quantity'] ?? null,
            FILTER_VALIDATE_INT
        );

        $userIdInput = $input['user_id'] ?? null;

        if (
            $userIdInput !== null
            && $userIdInput !== ''
            && !is_int($userIdInput)
            && !is_string($userIdInput)
        ) {
            throw new InvalidArgumentException('User ID tidak valid.');
        }

        $userId = $userIdInput === null || $userIdInput === ''
            ? null
            : filter_var($userIdInput, FILTER_VALIDATE_INT);

        $typeInput = $input['type'] ?? null;

        if (!is_string($typeInput)) {
            throw new InvalidArgumentException('Tipe transaksi tidak valid.');
        }

        $type = strtolower(trim($typeInput));

        $notesInput = $input['notes'] ?? '';

        if (!is_string($notesInput)) {
            throw new InvalidArgumentException('Format catatan tidak valid.');
        }

        $notes = trim($notesInput);

        if ($itemId === false || $itemId < 1) {
            throw new InvalidArgumentException('Barang harus dipilih.');
        }

        if (!in_array($type, ['stock_in', 'stock_out', 'adjustment'], true)) {
            throw new InvalidArgumentException('Tipe transaksi tidak valid.');
        }

        if (
            $quantity === false
            || $quantity < 0
            || ($type !== 'adjustment' && $quantity < 1)
        ) {
            throw new InvalidArgumentException(
                $type === 'adjustment'
                    ? 'Jumlah adjustment minimal 0.'
                    : 'Jumlah transaksi minimal 1.'
            );
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