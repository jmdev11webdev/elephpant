<?php

declare(strict_types=1);

/**
 * @param  list<int>  $numbers
 */
function findFirstDuplicate(array $numbers): ?int
{
    $seen = [];

    foreach ($numbers as $num) {
        if (isset($seen[$num])) {
            return $num;
        }

        $seen[$num] = true;
    }

    return null;
}
