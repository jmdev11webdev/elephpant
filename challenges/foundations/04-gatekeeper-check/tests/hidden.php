<?php

declare(strict_types=1);
require_once __DIR__.'/Gatekeeper.php';

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
                'error' => $passed ? null : 'Gatekeeper check did not match boundary expectations.',
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
        'name' => 'locks gate at zero XP',
        'expected' => 'Gate Locked',
        'run' => fn () => checkTownGate(0),
    ],
    [
        'name' => 'opens gate at high XP',
        'expected' => 'Gate Open',
        'run' => fn () => checkTownGate(999),
    ],
]);
