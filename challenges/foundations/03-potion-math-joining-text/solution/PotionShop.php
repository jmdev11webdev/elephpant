<?php

declare(strict_types=1);

function formatPotionReceipt(int $price, int $qty): string
{
    $totalCost = $price * $qty;

    return "{$qty} potions cost {$totalCost} coins";
}
