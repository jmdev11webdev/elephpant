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
        'name' => 'returns parameterized SQL with normalized email binding',
        'expected' => [
            'sql' => 'SELECT id, name, email FROM users WHERE email = ? LIMIT 1',
            'bindings' => ['ada@example.com'],
        ],
        'run' => fn () => buildSafeUserLookup('  Ada@Example.com '),
    ],
]);
