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
                'error' => $passed ? null : 'Function greet() did not return expected greeting.',
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
        'name' => 'greets a multi-word name',
        'expected' => 'Hello, Mentor Ada!',
        'run' => fn () => greet('Mentor Ada'),
    ],
    [
        'name' => 'handles tabbed whitespace around name',
        'expected' => 'Hello, Grace!',
        'run' => fn () => greet("\tGrace\n"),
    ],
]);
