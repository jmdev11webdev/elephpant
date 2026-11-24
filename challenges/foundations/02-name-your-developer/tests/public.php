<?php

declare(strict_types=1);
require_once __DIR__.'/Profile.php';

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
                'error' => $passed ? null : ($case['hint'] ?? 'Value did not match expected type or value.'),
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
        'name' => '$name is a non-empty string',
        'expected' => true,
        'hint' => 'Set $name to text inside quotes, like "Ada".',
        'run' => function (): bool {
            $profile = createDeveloperProfile();

            return is_string($profile['name'] ?? null) && trim($profile['name']) !== '';
        },
    ],
    [
        'name' => '$level is the integer 1',
        'expected' => 1,
        'hint' => 'Set $level = 1; without quotes so PHP treats it as an integer.',
        'run' => fn () => createDeveloperProfile()['level'] ?? null,
    ],
    [
        'name' => '$isReady is the boolean true',
        'expected' => true,
        'hint' => 'Set $isReady = true; without quotes so PHP treats it as a boolean.',
        'run' => fn () => createDeveloperProfile()['isReady'] ?? null,
    ],
]);
