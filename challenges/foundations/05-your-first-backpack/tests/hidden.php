<?php

declare(strict_types=1);
require_once __DIR__.'/Backpack.php';

function runCases(array $cases): void
{
    $results = [];
    foreach ($cases as $case) {
        try {
            $actual = ($case['run'])();
            $expected = $case['expected'];
            $passed = $actual === $expected;
            $results[] = [
                'name' => $case['name'],
                'passed' => $passed,
                'expected' => json_encode($expected),
                'actual' => json_encode($actual),
                'error' => $passed ? null : 'Backpack indexing or append behavior did not match.',
            ];
        } catch (Throwable $e) {
            $results[] = [
                'name' => $case['name'],
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
        'name' => 'works with a single-item backpack',
        'expected' => [
            'first' => 'Notebook',
            'items' => ['Notebook', 'Badge Case'],
        ],
        'run' => fn () => packBackpack(
            ['owner' => 'Taylor', 'items' => ['Notebook']],
            'Badge Case',
        ),
    ],
]);
