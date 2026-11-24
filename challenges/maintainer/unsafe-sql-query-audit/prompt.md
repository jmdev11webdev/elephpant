# Step 4: Unsafe SQL Query Audit

> **Concept Reference: Parameterized SQL Queries & Injection Prevention**
> Concatenating untrusted input into SQL strings enables SQL injection even when quotes are escaped manually. Separating the static SQL template (`WHERE email = :email`) from a typed `bindings` array guarantees the database driver treats user input strictly as data.

## Requirements

1. Audit and remediate `buildUserLookupQuery(string $email): array` in `UserLookupQuery.php`.
2. Return an array with `'sql'` containing a parameterized query placeholder (such as `:email` or `?`) and `'bindings'` holding the untrusted `$email` value separately.
3. Ensure `$email` is never concatenated or interpolated directly into the `'sql'` string.
4. Select the correct **Technical Decision** remediation strategy below before submitting.
5. **Note:** `$email` is passed in automatically by the test runner—do not hardcode email addresses.

## Example

```php
buildUserLookupQuery("ada@example.com");
// Returns:
// [
//     'sql' => 'SELECT id, name, email FROM users WHERE email = :email',
//     'bindings' => ['email' => 'ada@example.com'],
// ]
```
