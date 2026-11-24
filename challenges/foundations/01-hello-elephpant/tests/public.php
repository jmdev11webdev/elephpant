<?php

declare(strict_types=1);

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
                'error' => $passed ? null : ($case['hint'] ?? 'Output did not match what we expected.'),
            ];
        } catch (Throwable $e) {
            $results[] = [
                'name' => $case['name'],
                'passed' => false,
                'expected' => json_encode($case['expected']),
                'actual' => null,
                'error' => 'PHP error: '.$e->getMessage(),
            ];
        }
    }
    echo json_encode(['results' => $results], JSON_UNESCAPED_SLASHES).PHP_EOL;
}

runCases([
    [
        'name' => 'prints the exact greeting "Hello, elePHPant!"',
        'expected' => 'Hello, elePHPant!',
        'hint' => 'Make sure you echoed "Hello, elePHPant!" with capital H, capital PHP, a comma, and an exclamation mark.',
        'run' => function (): string {
            ob_start();
            include __DIR__.'/Hello.php';

            return trim((string) ob_get_clean());
        },
    ],
]);
