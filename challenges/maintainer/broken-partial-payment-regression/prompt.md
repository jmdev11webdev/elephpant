# Step 2: Broken Partial-Payment Regression Fix

> **Concept Reference: Accumulator Regressions & Guard Clauses**
> Replacing an additive accumulator (`+=`) with a plain assignment (`=`) inside a loop silently drops all prior entries. Always accumulate positive payments and clamp final balances at `0`.

## Requirements

1. Diagnose and fix `InvoiceBalance::remainingAfterPayments(int $invoiceTotalCents, array $paymentsCents): int` in `InvoiceBalance.php`.
2. Accumulate **all** positive payment entries (`$amount > 0`) rather than overwriting with only the last payment.
3. Ignore non-positive values (`<= 0`) such as refunds or zero-value adjustments.
4. Clamp the remaining balance at `0` so overpayments never return negative cents.
5. **Note:** `$invoiceTotalCents` and `$paymentsCents` are passed in automatically by the test runner—do not hardcode totals or payment arrays.

## Example

```php
InvoiceBalance::remainingAfterPayments(10000, [3000, -500, 4500]);
// Returns: 2500
```
