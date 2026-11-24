<?php

declare(strict_types=1);

/**
 * @param  list<int>  $coins
 */
function sumCoins(array $coins): int
{
    $total = 0;

    foreach ($coins as $coin) {
        $total += $coin;
    }

    return $total;
}
