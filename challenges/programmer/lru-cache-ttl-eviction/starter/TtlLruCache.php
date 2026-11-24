<?php

declare(strict_types=1);

class TtlLruCache
{
    public function __construct(
        private int $capacity,
    ) {}

    public function put(string $key, mixed $value, int $now, int $ttlSeconds): void
    {
        // TODO: Implement TTL-aware LRU insertion and eviction.
    }

    public function get(string $key, int $now): mixed
    {
        // TODO: Implement TTL check and recency promotion.
        return null;
    }
}
