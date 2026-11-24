# Step 2: Name Your Developer

> **Concept Reference: Variables & Scalar Types**
> Variables in PHP are prefixed with `$`. Common scalar types include `string` (quoted text), `int` (whole numbers without quotes), and `bool` (`true` or `false` without quotes).
>
> ```php
> $hero = 'Ada';
> $level = 1;
> $isReady = true;
> ```

## Requirements

1. Inside `createDeveloperProfile()` in `Profile.php`, assign a non-empty `string` value to `$name`.
2. Assign the integer `1` to `$level`.
3. Assign the boolean `true` to `$isReady`.
4. Return an associative array containing the exact keys `'name'`, `'level'`, and `'isReady'`.

## Example

```php
createDeveloperProfile();
// Returns:
// [
//     'name' => 'Ada',
//     'level' => 1,
//     'isReady' => true,
// ]
```
