<?php

declare(strict_types=1);
return [
 'driver' => 'mysql',
 'host' => Env::get('DB_HOST', '127.0.0.1'),
 'port' => Env::get('DB_PORT', '3306'),
 'database' => Env::get('DB_NAME', 'inventaris_k3'),
 'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
 'username' => Env::get('DB_USERNAME', 'root'),
 'password' => Env::get('DB_PASSWORD', ''),
];

