<?php

declare(strict_types=1);
require_once __DIR__.'/WelcomeRoute.php';

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
        'name' => 'trims surrounding whitespace from player name',
        'expected' => [
            'status' => 200,
            'body' => [
                'message' => 'Welcome to Localhost Town, Taylor!',
                'region' => 'The Stack Region',
                'state' => 'online',
            ],
        ],
        'run' => fn () => handleWelcomeRoute('  Taylor  '),
    ],
    [
        'name' => 'falls back to Adventurer when name is blank',
        'expected' => [
            'status' => 200,
            'body' => [
                'message' => 'Welcome to Localhost Town, Adventurer!',
                'region' => 'The Stack Region',
                'state' => 'online',
            ],
        ],
        'run' => fn () => handleWelcomeRoute('   '),
    ],
]);
