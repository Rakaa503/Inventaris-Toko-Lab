<?php

declare(strict_types=1);
return [
 'name' => Env::get('APP_NAME', 'Sistem Inventaris LAB'),
 'environment' => Env::get('APP_ENV', 'production'),
 'debug' => Env::get('APP_DEBUG', 'false') === 'true',
 'url' => Env::get('APP_URL', 'http://127.0.0.1:8081'),
 'timezone' => Env::get('APP_TIMEZONE', 'Asia/Jakarta'),
];

