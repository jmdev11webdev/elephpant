# Step 6: Count the Steps

> **Concept Reference: Iteration (`foreach` Loops)**
> A `foreach` loop iterates through every element in an array sequentially, making it ideal for accumulating totals across lists of arbitrary length.
>
> ```php
> $total = 0;
> foreach ([5, 10, 15] as $value) {
>     $total += $value;
> }
> // $total === 30
> ```

## Requirements

1. Implement `sumCoins(array $coins): int` in `CoinCounter.php`.
2. Initialize `$total = 0;` and iterate over `$coins` using `foreach` (or `for`).
3. Add each integer element to `$total` and return the final sum.
4. **Note:** `$coins` is passed in automatically by the test runner—do not overwrite it with a hardcoded array.

## Example

```php
sumCoins([5, 10, 25]); // Returns: 40
sumCoins([]);          // Returns: 0
```
