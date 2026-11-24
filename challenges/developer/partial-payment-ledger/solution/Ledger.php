<?php

declare(strict_types=1);

require_once __DIR__.'/Payment.php';

/**
 * @param  list<Payment>  $payments
 */
function calculateRemainingCents(int $invoiceTotalCents, array $payments): int
{
    $paid = 0;

    foreach ($payments as $payment) {
        if ($payment->status === 'settled' && $payment->amountCents > 0) {
            $paid += $payment->amountCents;
        }
    }

    return max(0, $invoiceTotalCents - $paid);
}
