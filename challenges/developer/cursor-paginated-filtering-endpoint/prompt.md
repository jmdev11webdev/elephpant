# Step 4: Cursor-Paginated Filtering Endpoint

> **Concept Reference: Keyset (Cursor) Pagination**
> Cursor pagination filters records strictly past the previous cursor (`id > $afterId`) and fetches `limit + 1` rows to determine whether a subsequent page exists without expensive `COUNT(*)` queries.

## Requirements

1. Implement `paginateOrders(array $orders, ?string $status, ?int $afterId, int $limit): array` in `OrderPaginator.php`, where each order has `['id' => int, 'status' => string]`.
2. Filter by `$status` when `$status !== null` and sort matching orders ascending by `'id'`.
3. Retain only orders with `id > $afterId` when `$afterId !== null`.
4. Return `['data' => list<order>, 'nextCursor' => ?int]` with at most `max(1, $limit)` items; set `nextCursor` to the last returned item's `'id'` only when more matching items remain (otherwise `null`).
5. **Note:** `$orders`, `$status`, `$afterId`, and `$limit` are passed in automatically by the test runner—do not hardcode parameters.

## Example

```php
paginateOrders(
    [['id' => 3, 'status' => 'paid'], ['id' => 1, 'status' => 'paid'], ['id' => 2, 'status' => 'paid']],
    status: 'paid',
    afterId: null,
    limit: 2,
);
// Returns:
// ['data' => [['id' => 1, 'status' => 'paid'], ['id' => 2, 'status' => 'paid']], 'nextCursor' => 2]
```
