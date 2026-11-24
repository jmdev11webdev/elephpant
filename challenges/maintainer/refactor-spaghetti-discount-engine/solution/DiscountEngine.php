<?php

declare(strict_types=1);

function calculateDiscountedTotal(int $subtotalCents, int $percentOff, int $couponCents): int
{
    if ($subtotalCents <= 0) {
        return 0;
    }

    $clampedPercent = max(0, min(100, $percentOff));
    $clampedCoupon = max(0, $couponCents);

    $rawDiscount = (int) round($subtotalCents * ($clampedPercent / 100)) + $clampedCoupon;
    $maxDiscount = (int) floor($subtotalCents * 0.5);

    return max(0, $subtotalCents - min($rawDiscount, $maxDiscount));
}
