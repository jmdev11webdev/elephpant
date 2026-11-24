<?php

declare(strict_types=1);

class InvoiceBalance
{
    /**
     * @param  list<int>  $partialPaymentsCents
     */
    public static function remainingAfterPayments(int $totalCents, array $partialPaymentsCents): int
    {
        $remaining = $totalCents;

        foreach ($partialPaymentsCents as $amount) {
            if ($amount > 0) {
                $remaining -= $amount;
            }
        }

        return max(0, $remaining);
    }
}
