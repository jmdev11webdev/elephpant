# Step 7: Build a Mini Tool

> **Concept Reference: Typed Functions & `return`**
> Functions encapsulate reusable logic with explicit parameter types and return types. Use `return` to pass the computed result back to the caller.
>
> ```php
> function shout(string $word): string
> {
>     return strtoupper($word).'!';
> }
> ```

## Requirements

1. Implement `greet(string $name): string` in `Greeter.php`.
2. Strip leading and trailing whitespace from `$name` using `trim($name)`.
3. Return the formatted greeting `"Hello, {$cleanName}!"`.
4. **Note:** `$name` is passed in automatically by the test runner—do not overwrite it with a hardcoded name.

## Example

```php
greet('Ada');        // Returns: 'Hello, Ada!'
greet('  Taylor  '); // Returns: 'Hello, Taylor!'
```
