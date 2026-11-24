<?php

declare(strict_types=1);
require_once __DIR__.'/Ledger.php';

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
        'name' => 'clamps overpayments to zero',
        'expected' => 0,
        'run' => fn () => calculateRemainingCents(5000, [
            new Payment(3000, 'settled'),
            new Payment(4000, 'settled'),
        ]),
    ],
    [
        'name' => 'ignores negative or zero amounts',
        'expected' => 5000,
        'run' => fn () => calculateRemainingCents(5000, [
            new Payment(-1000, 'settled'),
            new Payment(0, 'settled'),
        ]),
    ],
]);
