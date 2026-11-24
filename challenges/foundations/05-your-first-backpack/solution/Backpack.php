<?php

declare(strict_types=1);

/**
 * @param  array{owner: string, items: list<string>}  $bag
 * @return array{first: string, items: list<string>}
 */
function packBackpack(array $bag, string $newItem): array
{
    $items = $bag['items'];
    $first = $items[0];
    $items[] = $newItem;

    return [
        'first' => $first,
        'items' => $items,
    ];
}
