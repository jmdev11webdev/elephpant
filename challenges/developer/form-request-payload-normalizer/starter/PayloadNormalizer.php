<?php

declare(strict_types=1);

/**
 * @param  array{name?: string, email?: string, tags?: list<string>}  $input
 * @return array{name: string, email: string, tags: list<string>}
 */
function normalizeRegistrationPayload(array $input): array
{
    // TODO: Normalize name, email, and unique lowercase tags.
    return [
        'name' => '',
        'email' => '',
        'tags' => [],
    ];
}
