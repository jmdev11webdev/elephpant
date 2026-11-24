<?php

declare(strict_types=1);

require_once __DIR__.'/TtlLruCache.php';

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
        'name' => 'expires stale keys before evicting live entries',
        'expected' => [null, 'B', 'C'],
        'run' => function () {
            $cache = new TtlLruCache(2);
            $cache->put('a', 'A', 100, 5);
            $cache->put('b', 'B', 101, 50);
            $cache->put('c', 'C', 106, 50);

            return [$cache->get('a', 107), $cache->get('b', 107), $cache->get('c', 107)];
        },
    ],
]);
