<?php

declare(strict_types=1);
require_once __DIR__.'/Backpack.php';

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
                'error' => $passed ? null : ($case['hint'] ?? 'Backpack result did not match expected array.'),
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
        'name' => 'reads first item and appends Lantern to backpack',
        'expected' => [
            'first' => 'Map',
            'items' => ['Map', 'Potion', 'Lantern'],
        ],
        'hint' => 'Read $items[0] for "first" and use $items[] = $newItem; to append.',
        'run' => fn () => packBackpack(
            ['owner' => 'Ada', 'items' => ['Map', 'Potion']],
            'Lantern',
        ),
    ],
]);
