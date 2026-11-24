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
        'name' => 'isolates independent client buckets',
        'expected' => [true, false, true],
        'run' => function () {
            $limiter = new SlidingWindowLimiter(1, 60);

            return [
                $limiter->allow('a', 10),
                $limiter->allow('a', 20),
                $limiter->allow('b', 20),
            ];
        },
    ],
    [
        'name' => 'rejected hits do not consume future window quota',
        'expected' => [true, false, true],
        'run' => function () {
            $limiter = new SlidingWindowLimiter(1, 5);

            return [
                $limiter->allow('x', 1),
                $limiter->allow('x', 4),
                $limiter->allow('x', 6),
            ];
        },
    ],
]);
