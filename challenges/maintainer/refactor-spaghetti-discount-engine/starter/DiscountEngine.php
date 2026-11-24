<?php

declare(strict_types=1);

function calculateDiscountedTotal(int $subtotalCents, int $percentOff, int $couponCents): int
{
    // Legacy spaghetti: fails to cap combined discount at 50% and allows negative totals
    $total = $subtotalCents;
    if ($percentOff > 0) {
        $total -= (int) round($subtotalCents * ($percentOff / 100));
    }
    if ($couponCents > 0) {
        $total -= $couponCents;
    }

    return $total;
}
