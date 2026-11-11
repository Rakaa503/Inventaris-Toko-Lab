<?php

declare(strict_types=1);

final class AuthValidator
{
    private const MAX_IDENTIFIER_LENGTH = 254;
    private const MAX_PASSWORD_LENGTH = 4096;

    public static function validate(array $input): array
    {
        $identifier = $input['identifier'] ?? null;
        $password = $input['password'] ?? null;

        if (!is_string($identifier)) {
            throw new InvalidArgumentException(
                'Username atau email wajib diisi.'
            );
        }

        if (!is_string($password)) {
            throw new InvalidArgumentException(
                'Password wajib diisi.'
            );
        }

        $identifier = trim($identifier);

        if ($identifier === '') {
            throw new InvalidArgumentException(
                'Username atau email wajib diisi.'
            );
        }

        if (strlen($identifier) > self::MAX_IDENTIFIER_LENGTH) {
            throw new InvalidArgumentException(
                'Username atau email terlalu panjang.'
            );
        }

        if ($password === '') {
            throw new InvalidArgumentException(
                'Password wajib diisi.'
            );
        }

        if (strlen($password) > self::MAX_PASSWORD_LENGTH) {
            throw new InvalidArgumentException(
                'Password terlalu panjang.'
            );
        }

        return [
            'identifier' => $identifier,
            'password' => $password,
        ];
    }
}