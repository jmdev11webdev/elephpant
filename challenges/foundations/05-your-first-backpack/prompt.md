# Step 5: Your First Backpack

> **Concept Reference: Indexed & Associative Arrays**
> Arrays store ordered lists and key-value maps inside `[...]`. Indexed lists are zero-based (`$items[0]`), associative arrays are accessed by string key (`$bag['items']`), and `$items[] = $value;` appends a new element to the end of a list.
>
> ```php
> $items = ['Map', 'Compass'];
> $first = $items[0];       // 'Map'
> $items[] = 'Lantern';     // ['Map', 'Compass', 'Lantern']
> ```

## Requirements

1. Implement `packBackpack(array $bag, string $newItem): array` in `Backpack.php`.
2. Read the first element (`$items[0]`) from `$bag['items']` into `$first`.
3. Append `$newItem` to the end of `$items` using `$items[] = $newItem;`.
4. Return `['first' => $first, 'items' => $items]`.
5. **Note:** `$bag` and `$newItem` are passed in automatically by the test runner—do not overwrite them with hardcoded example values.

## Example

```php
packBackpack(
    ['owner' => 'Ada', 'items' => ['Map', 'Potion']],
    'Lantern',
);
// Returns:
// [
//     'first' => 'Map',
//     'items' => ['Map', 'Potion', 'Lantern'],
// ]
```
