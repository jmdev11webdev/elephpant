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
        'name' => 'finds earliest second occurrence',
        'expected' => 3,
        'run' => fn () => findFirstDuplicate([2, 1, 3, 5, 3, 2]),
    ],
    [
        'name' => 'returns null when all values are unique',
        'expected' => null,
        'run' => fn () => findFirstDuplicate([1, 2, 3, 4]),
    ],
]);
