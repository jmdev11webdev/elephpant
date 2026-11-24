# Step 3: Refactor Spaghetti Discount Engine

> **Concept Reference: Deterministic Pricing Rules & Boundary Clamping**
> Replacing nested conditionals with explicit input clamping (`max`/`min`) and integer rounding guarantees predictable pricing calculations and enforces maximum discount caps cleanly.

## Requirements

1. Refactor and fix `calculateDiscountedTotal(int $subtotalCents, int $percentOff, int $couponCents): int` in `DiscountEngine.php`.
2. Clamp `$percentOff` to `0..100` and `$couponCents` to `>= 0`.
3. Compute `$rawDiscount = (int) round($subtotalCents * ($percentOff / 100)) + $couponCents`.
4. Cap total discount at **50%** of `$subtotalCents` (`(int) floor($subtotalCents * 0.5)`) and return `max(0, $subtotalCents - min($rawDiscount, $maxDiscount))`.
5. **Note:** `$subtotalCents`, `$percentOff`, and `$couponCents` are passed in automatically by the test runner—do not hardcode input values.

## Example

```php
calculateDiscountedTotal(10000, percentOff: 20, couponCents: 500);
// Returns: 7500

calculateDiscountedTotal(10000, percentOff: 40, couponCents: 3000);
// Returns: 5000 (capped at 50% max discount)
```
