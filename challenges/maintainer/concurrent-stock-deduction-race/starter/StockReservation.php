<?php

declare(strict_types=1);

class StockReservation
{
    public static function deductAtomic(int &$availableStock, int $quantity): bool
    {
        // BUG: Deducts unconditionally and allows negative inventory!
        $availableStock -= $quantity;

        return true;
    }
}
