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
                'error' => $passed ? null : 'Script must define $devName, $town, and $badge and echo the exact sentence.',
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
        'name' => 'fixes both bugs cleanly without PHP warnings',
        'expected' => true,
        'run' => function (): bool {
            $hadWarning = false;
            set_error_handler(static function () use (&$hadWarning): bool {
                $hadWarning = true;

                return true;
            });

            ob_start();
            include __DIR__.'/BuggyScript.php';
            $output = trim((string) ob_get_clean());
            restore_error_handler();

            return ! $hadWarning && $output === 'Ada graduated in Localhost Town with Syntax Awakened!';
        },
    ],
]);
