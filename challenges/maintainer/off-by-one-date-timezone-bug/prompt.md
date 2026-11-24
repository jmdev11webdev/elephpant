# Step 1: Off-by-One Date & Timezone Bug

> **Concept Reference: `DateTimeImmutable` & Explicit Timezone Conversion**
> Parsing ISO-8601 UTC timestamps with `new DateTimeImmutable($utcIsoTimestamp, new DateTimeZone('UTC'))` and converting via `->setTimezone(new DateTimeZone($targetTimezone))` prevents midnight off-by-one bugs caused by host server timezone defaults.

## Requirements

1. Fix `formatLocalDate(string $utcIsoTimestamp, string $targetTimezone): string` in `DateFormatter.php`.
2. Parse `$utcIsoTimestamp` strictly in UTC and convert it to `$targetTimezone`.
3. Return the local calendar date formatted as `'Y-m-d'`.
4. **Note:** `$utcIsoTimestamp` and `$targetTimezone` are passed in automatically by the test runner—do not hardcode timestamps or timezones.

## Example

```php
formatLocalDate('2026-11-23T23:30:00Z', 'Asia/Manila');
// Returns: '2026-11-24'
```
