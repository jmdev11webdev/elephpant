<?php

declare(strict_types=1);
require_once __DIR__.'/PotionShop.php';

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
                'error' => $passed ? null : ($case['hint'] ?? 'Output did not match expected receipt.'),
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
        'name' => 'calculates 3 potions at 25 coins each',
        'expected' => '3 potions cost 75 coins',
        'hint' => 'Multiply $price * $qty (25 * 3 = 75) and return "{$qty} potions cost {$totalCost} coins".',
        'run' => fn () => formatPotionReceipt(25, 3),
    ],
    [
        'name' => 'calculates 5 potions at 10 coins each',
        'expected' => '5 potions cost 50 coins',
        'hint' => 'Do not reassign the incoming $price or $qty parameters with hardcoded values—compute $totalCost = $price * $qty directly from the parameters.',
        'run' => fn () => formatPotionReceipt(10, 5),
    ],
]);
