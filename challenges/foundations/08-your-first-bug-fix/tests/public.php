<?php

declare(strict_types=1);

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
                'error' => $passed ? null : ($case['hint'] ?? 'Output did not match expected graduation line.'),
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
        'name' => 'runs cleanly without syntax or variable errors and prints graduation line',
        'expected' => 'Ada graduated in Localhost Town with Syntax Awakened!',
        'hint' => 'Add the missing semicolon after $town = \'Localhost Town\'; and fix $devNmae to $devName.',
        'run' => function (): string {
            ob_start();
            include __DIR__.'/BuggyScript.php';

            return trim((string) ob_get_clean());
        },
    ],
]);
