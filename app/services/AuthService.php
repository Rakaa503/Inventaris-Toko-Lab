<?php

declare(strict_types=1);

final class AuthService
{
    private const SESSION_USER_KEY = 'user_id';

    public function __construct(
        private UserRepository $users
    ) {
    }

    public function login(
        string $identifier,
        string $password
    ): ?User {
        $this->ensureSession();

        $identifier = trim($identifier);

        if ($identifier === '' || $password === '') {
            return null;
        }

        $user = $this->users->findByUsername($identifier);

        if ($user === null) {
            $user = $this->users->findByEmail($identifier);
        }

        if ($user === null || !$user->isActive) {
            return null;
        }

        if (!$user->verifyPassword($password)) {
            return null;
        }

        if ($user->needsPasswordRehash()) {
            $this->users->updatePassword(
                $user->id,
                $password
            );

            $user = $this->users->findById($user->id);

            if ($user === null) {
                return null;
            }
        }

        if (!session_regenerate_id(true)) {
            throw new RuntimeException(
                'Gagal memperbarui session ID.'
            );
        }

        $_SESSION[self::SESSION_USER_KEY] = $user->id;
        $_SESSION['authenticated_at'] = time();

        Csrf::regenerate();

        return $user;
    }

    public function currentUser(): ?User
    {
        $this->ensureSession();

        $userId = $_SESSION[self::SESSION_USER_KEY] ?? null;

        if (
            !is_int($userId)
            && !(is_string($userId) && ctype_digit($userId))
        ) {
            return null;
        }

        $user = $this->users->findById((int) $userId);

        if ($user === null || !$user->isActive) {
            unset(
                $_SESSION[self::SESSION_USER_KEY],
                $_SESSION['authenticated_at']
            );

            return null;
        }

        return $user;
    }

    public function isAuthenticated(): bool
    {
        return $this->currentUser() !== null;
    }

    public function logout(): void
    {
        $this->ensureSession();

        unset(
            $_SESSION[self::SESSION_USER_KEY],
            $_SESSION['authenticated_at']
        );

        if (!session_regenerate_id(true)) {
            throw new RuntimeException(
                'Gagal memperbarui session ID saat logout.'
            );
        }

        Csrf::regenerate();
    }

    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException(
                'Session harus aktif untuk autentikasi.'
            );
        }
    }
}