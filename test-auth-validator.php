<?php

declare(strict_types=1);

require_once __DIR__ . '/app/validators/AuthValidator.php';

$passed = 0;
$failed = 0;

$check = static function (string $label, bool $condition) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        echo "[PASS] {$label}" . PHP_EOL;
    } else {
        $failed++;
        echo "[FAIL] {$label}" . PHP_EOL;
    }
};

$rejects = static function (array $input): bool {
    try {
        AuthValidator::validate($input);
        return false;
    } catch (InvalidArgumentException) {
        return true;
    }
};

$valid = AuthValidator::validate([
    'identifier' => '  admin.lab  ',
    'password' => 'TestPassword#2026',
]);

$check('Valid username accepted', $valid['identifier'] === 'admin.lab');
$check('Valid password preserved', $valid['password'] === 'TestPassword#2026');

$email = AuthValidator::validate([
    'identifier' => 'admin@example.test',
    'password' => 'TestPassword#2026',
]);

$check('Email identifier accepted', $email['identifier'] === 'admin@example.test');

$spacedPassword = '  Password With Spaces  ';

$result = AuthValidator::validate([
    'identifier' => 'admin.lab',
    'password' => $spacedPassword,
]);

$check('Password whitespace preserved', $result['password'] === $spacedPassword);

$check('Missing identifier rejected', $rejects([
    'password' => 'TestPassword#2026',
]));

$check('Empty identifier rejected', $rejects([
    'identifier' => '   ',
    'password' => 'TestPassword#2026',
]));

$check('Missing password rejected', $rejects([
    'identifier' => 'admin.lab',
]));

$check('Empty password rejected', $rejects([
    'identifier' => 'admin.lab',
    'password' => '',
]));

$check('Array identifier rejected', $rejects([
    'identifier' => [],
    'password' => 'TestPassword#2026',
]));

$check('Array password rejected', $rejects([
    'identifier' => 'admin.lab',
    'password' => [],
]));

$check('Identifier over maximum length rejected', $rejects([
    'identifier' => str_repeat('a', 255),
    'password' => 'TestPassword#2026',
]));

$check('Password over maximum length rejected', $rejects([
    'identifier' => 'admin.lab',
    'password' => str_repeat('a', 4097),
]));

echo PHP_EOL;
echo "========== AUTH VALIDATOR TEST SUMMARY ==========" . PHP_EOL;
echo "Passed: {$passed}" . PHP_EOL;
echo "Failed: {$failed}" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}