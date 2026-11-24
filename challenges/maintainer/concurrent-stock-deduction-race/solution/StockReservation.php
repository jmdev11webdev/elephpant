<?php

declare(strict_types=1);

class StockReservation
{
    public static function deductAtomic(int &$availableStock, int $quantity): bool
    {
        if ($quantity <= 0 || $availableStock < $quantity) {
            return false;
        }

        $availableStock -= $quantity;

        return true;
    }
}
