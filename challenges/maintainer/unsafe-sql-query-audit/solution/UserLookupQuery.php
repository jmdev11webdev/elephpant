<?php

declare(strict_types=1);

/**
 * @return array{sql: string, bindings: list<string>}
 */
function buildSafeUserLookup(string $email): array
{
    return [
        'sql' => 'SELECT id, name, email FROM users WHERE email = ? LIMIT 1',
        'bindings' => [strtolower(trim($email))],
    ];
}
