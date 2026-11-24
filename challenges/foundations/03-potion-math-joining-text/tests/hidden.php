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
                'error' => $passed ? null : 'Receipt math or formatting did not match.',
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
        'name' => 'handles zero quantity cleanly',
        'expected' => '0 potions cost 0 coins',
        'run' => fn () => formatPotionReceipt(40, 0),
    ],
    [
        'name' => 'handles larger order totals',
        'expected' => '12 potions cost 180 coins',
        'run' => fn () => formatPotionReceipt(15, 12),
    ],
]);
