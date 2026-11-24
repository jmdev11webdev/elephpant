# Step 1: Fix the Welcome Route

> **Concept Reference: Structured HTTP JSON Responses**
> API route handlers return consistent status codes and typed response payloads, trimming untrusted input and applying safe defaults when optional fields are blank.

## Requirements

1. Update `handleWelcomeRoute(string $playerName): array` in `WelcomeRoute.php`.
2. Return `'status' => 200` and a `'body'` array containing:
    - `'message'`: `"Welcome to Localhost Town, {$cleanName}!"` (fall back to `'Adventurer'` when `trim($playerName)` is empty).
    - `'region'`: `'The Stack Region'`.
    - `'state'`: `'online'`.
3. **Note:** `$playerName` is passed in automatically by the test runner—do not overwrite it with hardcoded example names.

## Example

```php
handleWelcomeRoute('Ada');
// Returns:
// [
//     'status' => 200,
//     'body' => [
//         'message' => 'Welcome to Localhost Town, Ada!',
//         'region' => 'The Stack Region',
//         'state' => 'online',
//     ],
// ]
```
