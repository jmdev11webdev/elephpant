<?php

declare(strict_types=1);

/**
 * @return array{name: string, level: int, isReady: bool}
 */
function createDeveloperProfile(): array
{
    $name = 'Ada';
    $level = 1;
    $isReady = true;

    return [
        'name' => $name,
        'level' => $level,
        'isReady' => $isReady,
    ];
}
