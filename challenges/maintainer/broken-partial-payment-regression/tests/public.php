<?php

declare(strict_types=1);
require_once __DIR__.'/InvoiceBalance.php';

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
        'name' => 'deducts all partial payments cumulatively',
        'expected' => 1500,
        'run' => fn () => InvoiceBalance::remainingAfterPayments(5000, [1500, 2000]),
    ],
    [
        'name' => 'handles single payment',
        'expected' => 3000,
        'run' => fn () => InvoiceBalance::remainingAfterPayments(5000, [2000]),
    ],
]);
