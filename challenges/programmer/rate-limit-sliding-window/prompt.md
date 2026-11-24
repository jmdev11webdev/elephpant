# Step 4: Rate-Limit Sliding Window

> **Concept Reference: Sliding-Window Queues**
> A sliding-window rate limiter maintains per-client request timestamps and evicts entries older than or equal to `$timestamp - $windowSeconds` before checking capacity.

## Requirements

1. Implement `SlidingWindowLimiter` in `RateLimiter.php` with `__construct(int $maxRequests, int $windowSeconds)`.
2. Implement `allow(string $clientId, int $timestamp): bool` to evaluate requests inside the half-open interval `($timestamp - $windowSeconds, $timestamp]`.
3. Evict all expired timestamps `<= $timestamp - $windowSeconds` for `$clientId`.
4. If the client has fewer than `$maxRequests` active timestamps, record `$timestamp` and return `true`; otherwise return `false`.
5. **Note:** Constructor arguments, `$clientId`, and `$timestamp` are passed in automatically by the test runner—do not overwrite them with hardcoded values.

## Example

```php
$limiter = new SlidingWindowLimiter(maxRequests: 2, windowSeconds: 10);
$limiter->allow('client-a', 1);  // Returns: true
$limiter->allow('client-a', 5);  // Returns: true
$limiter->allow('client-a', 8);  // Returns: false
$limiter->allow('client-a', 11); // Returns: true (t=1 expired)
```
