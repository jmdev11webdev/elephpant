# Step 2: Form Request Payload Normalizer

> **Concept Reference: Input Normalization Pipelines**
> Before validation rules execute, Laravel Form Requests normalize raw payloads—collapsing whitespace, lowercasing identifiers like emails, and deduplicating tag lists deterministically.

## Requirements

1. Implement `normalizeRegistrationPayload(array $input): array` in `PayloadNormalizer.php`.
2. Return `'name'` trimmed with repeated internal whitespace collapsed to a single space.
3. Return `'email'` trimmed and lowercased.
4. Return `'tags'` as a `list<string>` of trimmed, lowercased, non-empty, unique tags preserving first-seen order.
5. **Note:** `$input` is passed in automatically by the test runner—do not overwrite it with hardcoded payloads.

## Example

```php
normalizeRegistrationPayload([
    'name' => '  Ada   Lovelace ',
    'email' => ' ADA@Example.COM ',
    'tags' => [' PHP ', '', 'laravel', 'php'],
]);
// Returns:
// [
//     'name' => 'Ada Lovelace',
//     'email' => 'ada@example.com',
//     'tags' => ['php', 'laravel'],
// ]
```
