# Step 1: First Duplicate Finder

> **Concept Reference: Hash-Map Membership Lookups (`O(n)`)**
> Tracking previously visited values in an associative PHP array (`$seen[$value] = true`) gives `O(1)` average-time membership checks via `isset()`, avoiding `O(n²)` nested loops or order-destroying sorts.

## Requirements

1. Implement `findFirstDuplicate(array $numbers): ?int` in `DuplicateFinder.php`.
2. Scan `$numbers` from left to right without sorting or mutating the input order.
3. Return the first integer whose second occurrence appears earliest in the array, or `null` if every value is unique.
4. Handle negative integers, zero, positive integers, and empty arrays in `O(n)` time.
5. **Note:** `$numbers` is passed in automatically by the test runner—do not overwrite it with hardcoded example values.

## Example

```php
findFirstDuplicate([2, 1, 3, 5, 3, 2]); // Returns: 3
findFirstDuplicate([1, 2, 3, 4]);       // Returns: null
```
