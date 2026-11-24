<?php

declare(strict_types=1);
require_once __DIR__.'/UserLookupQuery.php';

function runCases(array $cases): void
{
    $results = [];
    foreach ($cases as $case) {
        $name = $case['name'];
        try {
            $actual = ($case['run'])();
            $expected = $case['expected'];
            $passed = $actual === $expected;
            $results[] = [
                'name' => $name,
                'passed' => $passed,
                'expected' => json_encode($expected),
                'actual' => json_encode($actual),
                'error' => null,
            ];
        } catch (Throwable $e) {
            $results[] = [
                'name' => $name,
                'passed' => false,
                'expected' => json_encode($case['expected']),
                'actual' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
    echo json_encode(['results' => $results], JSON_UNESCAPED_SLASHES).PHP_EOL;
}

runCases([
    [
        'name' => 'keeps SQL injection payload strictly inside bindings',
        'expected' => [
            'sql' => 'SELECT id, name, email FROM users WHERE email = ? LIMIT 1',
            'bindings' => ["' or '1'='1"],
        ],
        'run' => fn () => buildSafeUserLookup("' OR '1'='1"),
    ],
]);
