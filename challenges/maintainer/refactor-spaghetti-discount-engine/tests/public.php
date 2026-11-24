<?php

declare(strict_types=1);

require_once __DIR__.'/DiscountEngine.php';

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
        'name' => 'applies percentage and coupon below 50 percent cap',
        'expected' => 7500,
        'run' => fn () => calculateDiscountedTotal(10000, 15, 1000),
    ],
    [
        'name' => 'caps stacked discounts at 50 percent of subtotal',
        'expected' => 5000,
        'run' => fn () => calculateDiscountedTotal(10000, 40, 3000),
    ],
]);
