<?php

declare(strict_types=1);

require_once __DIR__.'/PayloadNormalizer.php';

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
        'name' => 'normalizes whitespace, email casing, and duplicate tags',
        'expected' => [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'tags' => ['php', 'laravel'],
        ],
        'run' => fn () => normalizeRegistrationPayload([
            'name' => '  Ada   Lovelace ',
            'email' => '  Ada@Example.COM ',
            'tags' => [' PHP ', 'laravel', 'php', '   '],
        ]),
    ],
]);
