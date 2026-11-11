<?php

declare(strict_types=1);

final class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_WINDOW_SECONDS = 900;

    public function __construct(
        private AuthService $auth,
        private RateLimiter $rateLimiter
    ) {
    }

    public function showLogin(): void
    {
        if ($this->auth->isAuthenticated()) {
            $this->redirect('/');
        }

        $this->render('auth/login', [
            'error' => null,
            'identifier' => '',
        ]);
    }

    public function login(): void
    {
        if ($this->auth->isAuthenticated()) {
            $this->redirect('/');
        }

        $token = $_POST['_token'] ?? '';

        if (!is_string($token) || !Csrf::verify($token)) {
            http_response_code(419);

            $this->render('auth/login', [
                'error' => 'Sesi formulir tidak valid atau sudah kedaluwarsa. Silakan muat ulang halaman.',
                'identifier' => '',
            ]);

            return;
        }

        try {
            $input = AuthValidator::validate($_POST);
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);

            $this->render('auth/login', [
                'error' => $exception->getMessage(),
                'identifier' => is_string($_POST['identifier'] ?? null)
                    ? trim($_POST['identifier'])
                    : '',
            ]);

            return;
        }

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateLimitKey = 'login:' . $ipAddress;

        if (!$this->rateLimiter->attempt(
            $rateLimitKey,
            self::MAX_LOGIN_ATTEMPTS,
            self::LOGIN_WINDOW_SECONDS
        )) {
            http_response_code(429);

            $this->render('auth/login', [
                'error' => 'Terlalu banyak percobaan login. Silakan coba kembali setelah 15 menit.',
                'identifier' => $input['identifier'],
            ]);

            return;
        }

        $user = $this->auth->login(
            $input['identifier'],
            $input['password']
        );

        if ($user === null) {
            http_response_code(401);

            $this->render('auth/login', [
                'error' => 'Username/email atau password tidak valid.',
                'identifier' => $input['identifier'],
            ]);

            return;
        }

        $this->redirect('/');
    }

    public function logout(): void
    {
        $token = $_POST['_token'] ?? '';

        if (!is_string($token) || !Csrf::verify($token)) {
            http_response_code(419);
            echo '419 Session Expired';
            return;
        }

        $this->auth->logout();

        $this->redirect('/login');
    }

    private function redirect(string $path): never
    {
        header('Location: ' . url($path), true, 303);
        exit;
    }
}