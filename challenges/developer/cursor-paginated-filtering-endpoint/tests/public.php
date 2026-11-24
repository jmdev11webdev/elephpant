<?php

declare(strict_types=1);

require_once __DIR__.'/OrderPaginator.php';

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
        'name' => 'returns filtered page and nextCursor when more rows exist',
        'expected' => [
            'data' => [
                ['id' => 10, 'status' => 'paid'],
                ['id' => 20, 'status' => 'paid'],
            ],
            'nextCursor' => 20,
        ],
        'run' => fn () => paginateOrders([
            ['id' => 30, 'status' => 'paid'],
            ['id' => 10, 'status' => 'paid'],
            ['id' => 15, 'status' => 'void'],
            ['id' => 20, 'status' => 'paid'],
        ], 'paid', null, 2),
    ],
]);
