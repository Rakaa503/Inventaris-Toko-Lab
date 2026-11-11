<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login - Sistem Inventaris LAB</title>
    <link rel="stylesheet" href="<?= e(url('css/auth.css')) ?>">
</head>
<body>
    <main class="login-panel">
        <p class="eyebrow">LAB INVENTORY SYSTEM</p>
        <h1>Masuk ke akun</h1>
        <p class="description">
            Gunakan akun terdaftar untuk mengakses sistem inventaris laboratorium.
        </p>

        <?php if (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(url('login')) ?>">
            <?= Csrf::field() ?>

            <label for="identifier">Username atau email</label>
            <input
                type="text"
                id="identifier"
                name="identifier"
                value="<?= e($identifier ?? '') ?>"
                maxlength="254"
                autocomplete="username"
                required
                autofocus
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">MASUK</button>
        </form>

        <p class="footer">SISTEM INVENTARIS LAB &middot; ACCESS CONTROL</p>
    </main>
</body>
</html>