<?php

declare(strict_types=1);
require_once __DIR__.'/Greeter.php';

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
                'error' => $passed ? null : ($case['hint'] ?? 'Greeting did not match expected output.'),
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
        'name' => 'greets Ada cleanly',
        'expected' => 'Hello, Ada!',
        'hint' => 'Return "Hello, {$cleanName}!" using the $name parameter.',
        'run' => fn () => greet('Ada'),
    ],
    [
        'name' => 'trims spaces before greeting Taylor',
        'expected' => 'Hello, Taylor!',
        'hint' => 'Do not overwrite the incoming $name parameter—call trim($name) on the parameter and interpolate it into the greeting.',
        'run' => fn () => greet('  Taylor  '),
    ],
]);
