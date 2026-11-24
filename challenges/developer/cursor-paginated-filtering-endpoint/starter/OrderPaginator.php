<?php

declare(strict_types=1);

/**
 * @param  list<array{id: int, status: string}>  $orders
 * @return array{data: list<array{id: int, status: string}>, nextCursor: ?int}
 */
function paginateOrders(array $orders, ?string $status, ?int $afterId, int $limit): array
{
    // TODO: Filter, sort by id asc, apply cursor, and compute nextCursor.
    return [
        'data' => [],
        'nextCursor' => null,
    ];
}
