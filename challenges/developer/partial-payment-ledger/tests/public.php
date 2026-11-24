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
        'name' => 'subtracts multiple settled partial payments',
        'expected' => 2500,
        'run' => fn () => calculateRemainingCents(10000, [
            new Payment(4000, 'settled'),
            new Payment(3500, 'settled'),
        ]),
    ],
    [
        'name' => 'ignores void payments',
        'expected' => 6000,
        'run' => fn () => calculateRemainingCents(10000, [
            new Payment(4000, 'settled'),
            new Payment(5000, 'void'),
        ]),
    ],
]);
