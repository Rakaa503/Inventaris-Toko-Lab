<?php

declare(strict_types=1);

final class RateLimiter
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory
            ?? ROOT_PATH . '/storage/rate-limits';

        if (
            !is_dir($this->directory)
            && !mkdir($this->directory, 0700, true)
            && !is_dir($this->directory)
        ) {
            throw new RuntimeException(
                'Direktori rate limiter tidak dapat dibuat.'
            );
        }

        if (!is_writable($this->directory)) {
            throw new RuntimeException(
                'Direktori rate limiter tidak dapat ditulis.'
            );
        }
    }

    public function attempt(
        string $key,
        int $maxAttempts,
        int $windowSeconds
    ): bool {
        if (
            $key === ''
            || $maxAttempts < 1
            || $windowSeconds < 1
        ) {
            throw new InvalidArgumentException(
                'Parameter rate limiter tidak valid.'
            );
        }

        $path = $this->directory
            . '/'
            . hash('sha256', $key)
            . '.json';

        $handle = fopen($path, 'c+');

        if ($handle === false) {
            throw new RuntimeException(
                'Penyimpanan rate limiter gagal dibuka.'
            );
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException(
                    'Gagal mengunci rate limiter.'
                );
            }

            rewind($handle);

            $contents = stream_get_contents($handle);

            if ($contents === false) {
                throw new RuntimeException(
                    'Gagal membaca data rate limiter.'
                );
            }

            $now = time();
            $state = $contents === ''
                ? []
                : json_decode($contents, true);

            if (!is_array($state)) {
                $state = [];
            }

            $startedAt = filter_var(
                $state['started_at'] ?? null,
                FILTER_VALIDATE_INT
            );

            $attempts = filter_var(
                $state['attempts'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (
                $startedAt === false
                || $attempts === false
                || $startedAt > $now
                || $attempts < 0
                || ($now - $startedAt) >= $windowSeconds
            ) {
                $startedAt = $now;
                $attempts = 0;
            }

            $allowed = $attempts < $maxAttempts;

            if ($allowed) {
                $attempts++;
            }

            $newState = json_encode([
                'started_at' => $startedAt,
                'attempts' => $attempts,
            ], JSON_THROW_ON_ERROR);

            rewind($handle);

            if (!ftruncate($handle, 0)) {
                throw new RuntimeException(
                    'Gagal memperbarui rate limiter.'
                );
            }

            if (fwrite($handle, $newState) === false) {
                throw new RuntimeException(
                    'Gagal menyimpan rate limiter.'
                );
            }

            fflush($handle);

            return $allowed;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function remaining(
        string $key,
        int $maxAttempts,
        int $windowSeconds
    ): int {
        if (
            $key === ''
            || $maxAttempts < 1
            || $windowSeconds < 1
        ) {
            throw new InvalidArgumentException(
                'Parameter rate limiter tidak valid.'
            );
        }

        $path = $this->directory
            . '/'
            . hash('sha256', $key)
            . '.json';

        if (!is_file($path)) {
            return $maxAttempts;
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException(
                'Data rate limiter tidak dapat dibaca.'
            );
        }

        try {
            if (!flock($handle, LOCK_SH)) {
                throw new RuntimeException(
                    'Gagal membaca kunci rate limiter.'
                );
            }

            $contents = stream_get_contents($handle);

            if ($contents === false || $contents === '') {
                return $maxAttempts;
            }

            $state = json_decode($contents, true);

            if (!is_array($state)) {
                return $maxAttempts;
            }

            $startedAt = filter_var(
                $state['started_at'] ?? null,
                FILTER_VALIDATE_INT
            );

            $attempts = filter_var(
                $state['attempts'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (
                $startedAt === false
                || $attempts === false
                || $startedAt > time()
                || $attempts < 0
                || (time() - $startedAt) >= $windowSeconds
            ) {
                return $maxAttempts;
            }

            return max(0, $maxAttempts - $attempts);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}