<?php

declare(strict_types=1);

require_once __DIR__.'/CycleDetector.php';

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
        'name' => 'returns false for acyclic package graph',
        'expected' => false,
        'run' => fn () => hasDependencyCycle([
            'app' => ['http', 'db'],
            'http' => ['psr'],
            'db' => ['psr'],
            'psr' => [],
        ]),
    ],
    [
        'name' => 'detects 3-node cycle',
        'expected' => true,
        'run' => fn () => hasDependencyCycle([
            'a' => ['b'],
            'b' => ['c'],
            'c' => ['a'],
        ]),
    ],
]);
