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
                'error' => $passed ? null : 'Profile keys and strict PHP types must match string, int, and bool.',
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
        'name' => 'returns exact keys name, level, and isReady with strict types',
        'expected' => true,
        'run' => function (): bool {
            $profile = createDeveloperProfile();

            return array_keys($profile) === ['name', 'level', 'isReady']
                && is_string($profile['name'])
                && trim($profile['name']) !== ''
                && $profile['level'] === 1
                && $profile['isReady'] === true;
        },
    ],
]);
