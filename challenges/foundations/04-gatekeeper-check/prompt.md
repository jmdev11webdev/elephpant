# Step 4: The Gatekeeper Check

> **Concept Reference: Conditional Statements (`if` / `else`)**
> Conditional branches evaluate comparison expressions such as `>=` (greater than or equal to) and `===` (strict equality) to select which code path executes.
>
> ```php
> if ($coins >= 50) {
>     return 'Can Buy';
> }
>
> return 'Need More Coins';
> ```

## Requirements

1. Implement `checkTownGate(int $xp): string` in `Gatekeeper.php`.
2. Return `'Gate Open'` when `$xp` is greater than or equal to `100` (`$xp >= 100`).
3. Return `'Gate Locked'` for all values below `100`.
4. **Note:** `$xp` is passed in automatically by the test runner—do not overwrite it with a hardcoded value.

## Example

```php
checkTownGate(100); // Returns: 'Gate Open'
checkTownGate(150); // Returns: 'Gate Open'
checkTownGate(99);  // Returns: 'Gate Locked'
```
