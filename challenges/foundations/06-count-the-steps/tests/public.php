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
                'error' => $passed ? null : ($case['hint'] ?? 'Total did not match the sum of coins.'),
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
        'name' => 'sums [5, 10, 25] to 40',
        'expected' => 40,
        'hint' => 'Loop with foreach ($coins as $coin) and add $total += $coin;.',
        'run' => fn () => sumCoins([5, 10, 25]),
    ],
    [
        'name' => 'returns 0 for an empty coin pouch',
        'expected' => 0,
        'hint' => 'Do not overwrite the incoming $coins parameter with a hardcoded array—when $coins is empty, $total should stay 0.',
        'run' => fn () => sumCoins([]),
    ],
]);
