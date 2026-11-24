<?php

declare(strict_types=1);

function checkTownGate(int $xp): string
{
    if ($xp >= 100) {
        return 'Gate Open';
    }

    return 'Gate Locked';
}
