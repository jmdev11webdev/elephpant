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
    // TODO: Enforce replay window and constant-time HMAC-SHA256 verification.
    return false;
}
