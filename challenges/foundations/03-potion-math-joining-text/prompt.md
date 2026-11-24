# Step 3: Potion Math & Joining Text

> **Concept Reference: Arithmetic Operators & String Interpolation**
> PHP supports standard arithmetic operators (`+`, `-`, `*`, `/`). Double-quoted strings support variable interpolation (`"{$value}"`), and strings can also be joined with the concatenation operator (`.`).
>
> ```php
> $cost = 10 * 3;
> $summary = "Total: {$cost} coins"; // "Total: 30 coins"
> ```

## Requirements

1. Implement `formatPotionReceipt(int $price, int $qty): string` in `PotionShop.php`.
2. Compute `$totalCost = $price * $qty;` using the incoming parameters.
3. Return the formatted string `"{$qty} potions cost {$totalCost} coins"`.
4. **Note:** `$price` and `$qty` are passed in automatically by the test runner—do not overwrite them with hardcoded example values.

## Example

```php
formatPotionReceipt(25, 3);
// Returns: "3 potions cost 75 coins"

formatPotionReceipt(10, 5);
// Returns: "5 potions cost 50 coins"
```
