<?php

declare(strict_types=1);

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $username,
        public readonly string $email,
        private readonly string $passwordHash,
        public readonly string $role,
        public readonly bool $isActive,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {
        if ($this->id < 1) {
            throw new InvalidArgumentException('ID pengguna tidak valid.');
        }

        if (
            trim($this->name) === ''
            || trim($this->username) === ''
            || trim($this->email) === ''
            || trim($this->role) === ''
        ) {
            throw new InvalidArgumentException(
                'Data identitas pengguna tidak lengkap.'
            );
        }

        if ($this->passwordHash === '') {
            throw new InvalidArgumentException(
                'Password hash tidak boleh kosong.'
            );
        }
    }

    public static function fromArray(array $data): self
    {
        $requiredFields = [
            'id',
            'name',
            'username',
            'email',
            'password',
            'role',
            'is_active',
        ];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new UnexpectedValueException(
                    "Kolom pengguna tidak ditemukan: {$field}"
                );
            }
        }

        if (!is_numeric($data['id'])) {
            throw new UnexpectedValueException(
                'ID pengguna harus berupa angka.'
            );
        }

        $isActive = filter_var(
            $data['is_active'],
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        if ($isActive === null) {
            throw new UnexpectedValueException(
                'Status aktif pengguna tidak valid.'
            );
        }

        foreach (['name', 'username', 'email', 'password', 'role'] as $field) {
            if (!is_string($data[$field])) {
                throw new UnexpectedValueException(
                    "Kolom {$field} harus berupa string."
                );
            }
        }

        return new self(
            id: (int) $data['id'],
            name: $data['name'],
            username: $data['username'],
            email: $data['email'],
            passwordHash: $data['password'],
            role: $data['role'],
            isActive: $isActive,
            createdAt: isset($data['created_at'])
                ? (string) $data['created_at']
                : null,
            updatedAt: isset($data['updated_at'])
                ? (string) $data['updated_at']
                : null
        );
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function needsPasswordRehash(): bool
    {
        return password_needs_rehash(
            $this->passwordHash,
            PASSWORD_DEFAULT
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}