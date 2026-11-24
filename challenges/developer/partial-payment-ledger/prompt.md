# Step 3: Partial Payment Ledger

> **Concept Reference: Integer Cents & Value-Object Domain Services**
> Financial services represent currency in integer minor units (cents) and encapsulate payment state in typed value objects (`Payment`) to prevent floating-point drift and negative balances.

## Requirements

1. Use `Payment` (`Payment.php`) and implement `calculateRemainingCents(int $invoiceTotalCents, array $payments): int` in `Ledger.php`.
2. Subtract only `'settled'` payments whose `amountCents > 0`.
3. Ignore `'void'` payments and any non-positive amounts.
4. Clamp the remaining balance at `0` so overpayments never produce a negative integer.
5. **Note:** `$invoiceTotalCents` and `$payments` are passed in automatically by the test runner—do not hardcode invoice totals or payment lists.

## Example

```php
calculateRemainingCents(10000, [
    new Payment(4000, 'settled'),
    new Payment(2500, 'void'),
    new Payment(3000, 'settled'),
]);
// Returns: 3000
```
