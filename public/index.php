<?php

declare(strict_types=1);

// Front controller: bootstrap, security middleware, router, response.
// Implementasikan sebelum aplikasi dijalankan.
http_response_code(503);
header('Content-Type: text/plain; charset=UTF-8');
echo 'Aplikasi belum diimplementasikan. Silakan ikuti README.md.';