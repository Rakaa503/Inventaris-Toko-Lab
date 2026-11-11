<?php

declare(strict_types=1);

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        $view = trim($view);

        if (
            $view === ''
            || str_contains($view, '..')
            || !preg_match('/^[a-zA-Z0-9_\/-]+$/', $view)
        ) {
            throw new InvalidArgumentException('Nama view tidak valid.');
        }

        $viewPath = ROOT_PATH
            . '/app/views/'
            . $view
            . '.php';

        if (!is_file($viewPath) || !is_readable($viewPath)) {
            throw new RuntimeException('View tidak ditemukan.');
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $viewPath;

            $output = ob_get_clean();

            if ($output === false) {
                throw new RuntimeException('Gagal memproses output view.');
            }

            header('Content-Type: text/html; charset=UTF-8');

            echo $output;
        } catch (Throwable $exception) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            throw $exception;
        }
    }
}