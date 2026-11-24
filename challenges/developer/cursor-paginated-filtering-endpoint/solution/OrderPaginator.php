<?php

declare(strict_types=1);

/**
 * @param  list<array{id: int, status: string}>  $orders
 * @return array{data: list<array{id: int, status: string}>, nextCursor: ?int}
 */
function paginateOrders(array $orders, ?string $status, ?int $afterId, int $limit): array
{
    $pageSize = max(1, $limit);

    $filtered = array_values(array_filter(
        $orders,
        static function (array $order) use ($status, $afterId): bool {
            if ($status !== null && $order['status'] !== $status) {
                return false;
            }

            if ($afterId !== null && $order['id'] <= $afterId) {
                return false;
            }

            return true;
        },
    ));

    usort($filtered, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);

    $hasMore = count($filtered) > $pageSize;
    $page = array_slice($filtered, 0, $pageSize);
    $last = $page !== [] ? $page[count($page) - 1] : null;

    return [
        'data' => $page,
        'nextCursor' => ($hasMore && $last !== null) ? $last['id'] : null,
    ];
}
