<?php

declare(strict_types=1);

require_once __DIR__.'/Payment.php';

/**
 * @param  list<Payment>  $payments
 */
function calculateRemainingCents(int $invoiceTotalCents, array $payments): int
{
    // TODO: Apply settled positive payments and clamp at 0.
    return $invoiceTotalCents;
}
