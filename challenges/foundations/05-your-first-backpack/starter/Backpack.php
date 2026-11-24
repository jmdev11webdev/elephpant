<?php

declare(strict_types=1);

/**
 * @param  array{owner: string, items: list<string>}  $bag
 * @return array{first: string, items: list<string>}
 */
function packBackpack(array $bag, string $newItem): array
{
    // Note: $bag and $newItem are passed in automatically by the test runner.
    // Do not overwrite them with hardcoded example values.
    $items = $bag['items'];

    // Step 1: Read the first item at index 0 from $items
    $first = '';

    // Step 2: Append $newItem to the end of $items using $items[] = ...

    return [
        'first' => $first,
        'items' => $items,
    ];
}
