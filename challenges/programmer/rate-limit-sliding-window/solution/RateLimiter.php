<?php

declare(strict_types=1);

class SlidingWindowLimiter
{
    /** @var array<string, list<int>> */
    private array $hits = [];

    public function __construct(
        private int $maxRequests,
        private int $windowSeconds,
    ) {}

    public function allow(string $clientId, int $timestamp): bool
    {
        $cutoff = $timestamp - $this->windowSeconds;
        $active = array_values(array_filter(
            $this->hits[$clientId] ?? [],
            static fn (int $ts): bool => $ts > $cutoff,
        ));

        if (count($active) >= $this->maxRequests) {
            $this->hits[$clientId] = $active;

            return false;
        }

        $active[] = $timestamp;
        $this->hits[$clientId] = $active;

        return true;
    }
}
