# Step 5: Concurrent Stock Deduction Race

> **Concept Reference: Atomic Conditional Updates & Pessimistic Locking**
> Reading inventory in PHP, checking availability in memory, and writing back in a separate query creates a check-then-act race condition that oversells stock. Enforcing an atomic conditional check (`available >= quantity`) inside a locked transaction prevents negative inventory.

## Requirements

1. Fix `StockReservation::deductAtomic(int $quantity): bool` in `StockReservation.php`.
2. Reject non-positive quantities (`$quantity <= 0`) by returning `false` without altering stock.
3. Deduct `$quantity` and return `true` only when `$this->available >= $quantity`; otherwise leave stock unchanged and return `false`.
4. Select the correct **Technical Decision** concurrency strategy below before submitting.
5. **Note:** Initial stock and `$quantity` are passed in automatically by the test runner—do not hardcode inventory values.

## Example

```php
$reservation = new StockReservation(available: 5);
$reservation->deductAtomic(3); // Returns: true  (remaining: 2)
$reservation->deductAtomic(4); // Returns: false (remaining: 2)
```
