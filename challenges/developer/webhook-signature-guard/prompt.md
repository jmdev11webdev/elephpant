# Step 5: Webhook Signature Guard

> **Concept Reference: Constant-Time HMAC Verification & Replay Protection**
> Signed webhooks combine a timestamp tolerance check (`abs($now - $timestamp) <= $toleranceSeconds`) with `hash_hmac('sha256', ...)` and constant-time comparison via `hash_equals()` to defeat replay and timing attacks.

## Requirements

1. Implement `verifyWebhookSignature(string $payload, string $secret, int $timestamp, string $signature, int $now, int $toleranceSeconds = 300): bool` in `WebhookGuard.php`.
2. Return `false` immediately if `abs($now - $timestamp) > $toleranceSeconds`.
3. Compute the expected HMAC-SHA256 hex digest over `"{$timestamp}.{$payload}"` using `$secret`.
4. Compare `$signature` against the expected digest using `hash_equals()`.
5. **Note:** All parameters are passed in automatically by the test runner—do not hardcode secrets, timestamps, or signatures.

## Example

```php
$sig = hash_hmac('sha256', '1700000000.{"event":"paid"}', 'whsec_test');
verifyWebhookSignature('{"event":"paid"}', 'whsec_test', 1700000000, $sig, 1700000050);
// Returns: true
```
