# Step 2: Balanced Syntax Delimiters

> **Concept Reference: LIFO Stack Validation**
> A Last-In, First-Out (LIFO) stack matches nested opening delimiters (`(`, `[`, `{`) against their corresponding closing delimiters (`)`, `]`, `}`) in a single linear pass.

## Requirements

1. Implement `hasBalancedDelimiters(string $expression): bool` in `DelimiterChecker.php`.
2. Validate that all `()`, `[]`, and `{}` pairs in `$expression` open and close in strict LIFO order.
3. Ignore all non-delimiter characters (letters, digits, operators, whitespace).
4. Return `true` when balanced (including empty strings), and `false` otherwise.
5. **Note:** `$expression` is passed in automatically by the test runner—do not overwrite it with hardcoded example strings.

## Example

```php
hasBalancedDelimiters('fn([1 + 2]) { return (3); }'); // Returns: true
hasBalancedDelimiters('([)]');                        // Returns: false
```
