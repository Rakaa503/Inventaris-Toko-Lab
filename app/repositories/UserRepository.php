<?php

declare(strict_types=1);

final class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?User
    {
        if ($id < 1) {
            return null;
        }

        $statement = $this->pdo->prepare(
            'SELECT id, name, username, email, password, role,
                    is_active, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user === false ? null : User::fromArray($user);
    }

    public function findByUsername(string $username): ?User
    {
        $username = trim($username);

        if ($username === '') {
            return null;
        }

        $statement = $this->pdo->prepare(
            'SELECT id, name, username, email, password, role,
                    is_active, created_at, updated_at
             FROM users
             WHERE username = :username
             LIMIT 1'
        );

        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        return $user === false ? null : User::fromArray($user);
    }

    public function findByEmail(string $email): ?User
    {
        $email = trim($email);

        if ($email === '') {
            return null;
        }

        $statement = $this->pdo->prepare(
            'SELECT id, name, username, email, password, role,
                    is_active, created_at, updated_at
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user === false ? null : User::fromArray($user);
    }

    public function findAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, name, username, email, password, role,
                    is_active, created_at, updated_at
             FROM users
             ORDER BY name ASC'
        );

        $users = [];

        foreach ($statement->fetchAll() as $user) {
            $users[] = User::fromArray($user);
        }

        return $users;
    }

    public function create(array $data): int
    {
        foreach (['name', 'username', 'email', 'password', 'role'] as $field) {
            if (
                !isset($data[$field])
                || !is_string($data[$field])
                || trim($data[$field]) === ''
            ) {
                throw new InvalidArgumentException(
                    "Data pengguna tidak valid: {$field}"
                );
            }
        }

        $name = trim($data['name']);
        $username = trim($data['username']);
        $email = trim($data['email']);
        $password = $data['password'];
        $role = trim($data['role']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Format email tidak valid.');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password minimal 8 karakter.');
        }

        if (!in_array($role, ['admin', 'petugas', 'viewer'], true)) {
            throw new InvalidArgumentException('Role pengguna tidak valid.');
        }

        $isActive = $data['is_active'] ?? true;

        if (!is_bool($isActive) && !in_array($isActive, [0, 1, '0', '1'], true)) {
            throw new InvalidArgumentException('Status aktif pengguna tidak valid.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false) {
            throw new RuntimeException('Password gagal diproses.');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO users (
                name, username, email, password, role, is_active
             ) VALUES (
                :name, :username, :email, :password, :role, :is_active
             )'
        );

        $statement->execute([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $passwordHash,
            'role' => $role,
            'is_active' => (int) $isActive,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        if ($id < 1) {
            throw new InvalidArgumentException('ID pengguna tidak valid.');
        }

        foreach (['name', 'username', 'email', 'role'] as $field) {
            if (
                !isset($data[$field])
                || !is_string($data[$field])
                || trim($data[$field]) === ''
            ) {
                throw new InvalidArgumentException(
                    "Data pengguna tidak valid: {$field}"
                );
            }
        }

        $name = trim($data['name']);
        $username = trim($data['username']);
        $email = trim($data['email']);
        $role = trim($data['role']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Format email tidak valid.');
        }

        if (!in_array($role, ['admin', 'petugas', 'viewer'], true)) {
            throw new InvalidArgumentException('Role pengguna tidak valid.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE users SET
                name = :name,
                username = :username,
                email = :email,
                role = :role
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'role' => $role,
        ]);

        return $statement->rowCount() > 0;
    }

    public function updatePassword(int $id, string $password): bool
    {
        if ($id < 1) {
            throw new InvalidArgumentException('ID pengguna tidak valid.');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password minimal 8 karakter.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false) {
            throw new RuntimeException('Password gagal diproses.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE users
             SET password = :password
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'password' => $passwordHash,
        ]);

        return $statement->rowCount() > 0;
    }

    public function updateStatus(int $id, bool $isActive): bool
    {
        if ($id < 1) {
            throw new InvalidArgumentException('ID pengguna tidak valid.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE users
             SET is_active = :is_active
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'is_active' => (int) $isActive,
        ]);

        return $statement->rowCount() > 0;
    }
}