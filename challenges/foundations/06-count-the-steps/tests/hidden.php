<?php

declare(strict_types=1);
require_once __DIR__.'/CoinCounter.php';

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
                'error' => $passed ? null : 'Coin sum did not match.',
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
        'name' => 'sums a longer list of coin drops',
        'expected' => 115,
        'run' => fn () => sumCoins([1, 4, 10, 20, 30, 50]),
    ],
    [
        'name' => 'handles a single coin',
        'expected' => 7,
        'run' => fn () => sumCoins([7]),
    ],
]);
