<?php

declare(strict_types=1);

readonly class Payment
{
    public function __construct(
        public int $amountCents,
        public string $status = 'settled',
    ) {}
}
