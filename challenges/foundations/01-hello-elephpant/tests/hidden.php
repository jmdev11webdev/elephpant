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
                'error' => $passed ? null : 'Script must use echo and output only "Hello, elePHPant!".',
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
        'name' => 'uses PHP echo statement and outputs exact string without extra text',
        'expected' => true,
        'run' => function (): bool {
            $source = (string) file_get_contents(__DIR__.'/Hello.php');
            ob_start();
            include __DIR__.'/Hello.php';
            $output = trim((string) ob_get_clean());

            return str_contains($source, 'echo') && $output === 'Hello, elePHPant!';
        },
    ],
]);
