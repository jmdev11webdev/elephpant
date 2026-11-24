<?php

declare(strict_types=1);
require_once __DIR__.'/WebhookGuard.php';

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
        'name' => 'accepts valid fresh signature',
        'expected' => true,
        'run' => function () {
            $sig = hash_hmac('sha256', '1700000100.{"event":"paid"}', 'whsec_test');

            return verifyWebhookSignature('{"event":"paid"}', 'whsec_test', 1700000100, $sig, 1700000150);
        },
    ],
    [
        'name' => 'rejects expired timestamp outside tolerance',
        'expected' => false,
        'run' => function () {
            $sig = hash_hmac('sha256', '1700000000.{"event":"paid"}', 'whsec_test');

            return verifyWebhookSignature('{"event":"paid"}', 'whsec_test', 1700000000, $sig, 1700000500);
        },
    ],
]);
