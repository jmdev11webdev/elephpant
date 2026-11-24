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
        'name' => 'handles negative coupon and over-100 percent safely',
        'expected' => 5000,
        'run' => fn () => calculateDiscountedTotal(10000, 150, -500),
    ],
    [
        'name' => 'returns zero for zero subtotal',
        'expected' => 0,
        'run' => fn () => calculateDiscountedTotal(0, 20, 500),
    ],
]);
