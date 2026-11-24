<?php

declare(strict_types=1);

function checkTownGate(int $xp): string
{
    // Note: $xp is passed in automatically by the test runner.
    // Do not overwrite $xp with a hardcoded example value.

    // Step 1: Return 'Gate Open' when $xp >= 100
    // Step 2: Otherwise return 'Gate Locked'

    return 'Gate Locked';
}
