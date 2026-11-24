<?php

declare(strict_types=1);

require_once __DIR__.'/DateFormatter.php';

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
        'name' => 'converts early-morning UTC to previous day in America/New_York',
        'expected' => '2026-11-22',
        'run' => fn () => formatLocalDate('2026-11-23T02:15:00Z', 'America/New_York'),
    ],
    [
        'name' => 'preserves same day in UTC',
        'expected' => '2026-11-23',
        'run' => fn () => formatLocalDate('2026-11-23T12:00:00Z', 'UTC'),
    ],
]);
