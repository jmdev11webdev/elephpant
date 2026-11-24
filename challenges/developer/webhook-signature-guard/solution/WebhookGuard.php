<?php

declare(strict_types=1);

function verifyWebhookSignature(
    string $payload,
    string $secret,
    int $timestamp,
    string $signature,
    int $now,
    int $toleranceSeconds = 300,
): bool {
    if ($secret === '' || $signature === '') {
        return false;
    }

    if (abs($now - $timestamp) > $toleranceSeconds) {
        return false;
    }

    $expected = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

    return hash_equals($expected, $signature);
}
