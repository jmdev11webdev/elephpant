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
                'error' => $passed ? null : ($case['hint'] ?? 'Conditional check returned unexpected text.'),
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
        'name' => 'opens gate when XP is exactly 100',
        'expected' => 'Gate Open',
        'hint' => 'Use >= 100 (not > 100) so exactly 100 XP also opens the gate.',
        'run' => fn () => checkTownGate(100),
    ],
    [
        'name' => 'opens gate when XP is above 100',
        'expected' => 'Gate Open',
        'hint' => 'Return "Gate Open" when $xp >= 100.',
        'run' => fn () => checkTownGate(250),
    ],
    [
        'name' => 'keeps gate locked when XP is 99',
        'expected' => 'Gate Locked',
        'hint' => 'Do not overwrite the incoming $xp parameter—return "Gate Locked" when $xp is less than 100.',
        'run' => fn () => checkTownGate(99),
    ],
]);
