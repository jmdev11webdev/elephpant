<?php

declare(strict_types=1);

/**
 * @return array{sql: string, bindings: list<string>}
 */
function buildSafeUserLookup(string $email): array
{
    // VULNERABLE: Direct string interpolation into SQL!
    return [
        'sql' => "SELECT id, name, email FROM users WHERE email = '{$email}' LIMIT 1",
        'bindings' => [],
    ];
}
