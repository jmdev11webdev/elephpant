<?php

declare(strict_types=1);
require_once __DIR__.'/Solution.php';

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
        'name' => 'handles empty array',
        'expected' => null,
        'run' => fn () => findFirstDuplicate([]),
    ],
    [
        'name' => 'handles negative numbers and zero',
        'expected' => 0,
        'run' => fn () => findFirstDuplicate([-5, 0, 4, 0, -5]),
    ],
    [
        'name' => 'handles adjacent duplicate at start',
        'expected' => -1,
        'run' => fn () => findFirstDuplicate([-1, -1, 2, 2]),
    ],
]);
