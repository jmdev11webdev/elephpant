<?php

declare(strict_types=1);

class SlidingWindowLimiter
{
    public function __construct(
        private int $maxRequests,
        private int $windowSeconds,
    ) {}

    public function allow(string $clientId, int $timestamp): bool
    {
        // TODO: Implement sliding-window admission control.
        return false;
    }
}
