<?php

declare(strict_types=1);

function handleWelcomeRoute(string $playerName): array
{
    $name = trim($playerName);
    if ($name === '') {
        $name = 'Adventurer';
    }

    return [
        'status' => 200,
        'body' => [
            'message' => "Welcome to Localhost Town, {$name}!",
            'region' => 'The Stack Region',
            'state' => 'online',
        ],
    ];
}
