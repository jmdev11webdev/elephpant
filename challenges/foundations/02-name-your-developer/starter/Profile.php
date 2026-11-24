<?php

declare(strict_types=1);

/**
 * @return array{name: string, level: int, isReady: bool}
 */
function createDeveloperProfile(): array
{
    // Step 1: Set $name to a non-empty string (e.g. "Ada")
    $name = '';

    // Step 2: Set $level to the integer 1
    $level = 0;

    // Step 3: Set $isReady to the boolean true
    $isReady = false;

    return [
        'name' => $name,
        'level' => $level,
        'isReady' => $isReady,
    ];
}
