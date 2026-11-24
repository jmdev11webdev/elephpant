<?php

declare(strict_types=1);
require_once __DIR__.'/RateLimiter.php';

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
        'name' => 'allows up to maxRequests within window and blocks next',
        'expected' => [true, true, false, true],
        'run' => function () {
            $limiter = new SlidingWindowLimiter(2, 10);

            return [
                $limiter->allow('cli-1', 100),
                $limiter->allow('cli-1', 105),
                $limiter->allow('cli-1', 109),
                $limiter->allow('cli-1', 110),
            ];
        },
    ],
]);
