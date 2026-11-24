# Step 5: LRU Cache with TTL Eviction

> **Concept Reference: Ordered Hash Maps & TTL Expiration**
> PHP associative arrays preserve insertion order, enabling an LRU (Least Recently Used) cache by deleting and re-inserting keys on access while purging entries whose `expiresAt <= $now`.

## Requirements

1. Implement `TtlLruCache` in `TtlLruCache.php` with `__construct(int $capacity)`.
2. Implement `put(string $key, mixed $value, int $now, int $ttlSeconds): void`:
    - Purge all entries with `expiresAt <= $now`.
    - Insert or update `$key` with `expiresAt = $now + $ttlSeconds` and mark it most-recently used.
    - If the active entry count exceeds `$capacity`, evict the least-recently-used entry.
3. Implement `get(string $key, int $now): mixed`:
    - Return `null` if `$key` is missing or expired (`expiresAt <= $now`), removing expired keys.
    - Otherwise promote `$key` to most-recently used and return its stored value.
4. **Note:** `$capacity`, `$key`, `$value`, `$now`, and `$ttlSeconds` are passed in automatically by the test runner—do not hardcode values.

## Example

```php
$cache = new TtlLruCache(capacity: 2);
$cache->put('a', 1, now: 100, ttlSeconds: 10);
$cache->get('a', now: 105); // Returns: 1
$cache->get('a', now: 110); // Returns: null (expired)
```
