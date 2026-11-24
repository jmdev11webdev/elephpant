<?php

declare(strict_types=1);

function handleWelcomeRoute(string $playerName): array
{
    return [
        'status' => 500,
        'body' => [
            'message' => 'Welcome, '.$playerName,
            'region' => 'Unknown',
            'state' => 'offline',
        ],
    ];
}
