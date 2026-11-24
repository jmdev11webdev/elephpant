# Step 8: Your First Bug Fix

> **Concept Reference: Reading PHP Parse & Runtime Errors**
> PHP diagnostics report both the error category and line number:
>
> - `Parse error: syntax error, unexpected variable ...` typically indicates a missing semicolon (`;`) on the **preceding line**.
> - `Undefined variable $...` indicates a misspelled or uninitialized variable identifier (PHP variable names are case-sensitive).

## Requirements

1. Inspect `BuggyScript.php` and fix the missing semicolon (`;`).
2. Correct the misspelled variable name in the `echo` statement so it references `$devName`.
3. Ensure the script executes with zero warnings and prints the exact graduation sentence.

## Example

```php
// Expected stdout output:
Ada graduated in Localhost Town with Syntax Awakened!
```
