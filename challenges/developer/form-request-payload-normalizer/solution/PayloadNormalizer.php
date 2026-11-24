<?php

declare(strict_types=1);

/**
 * @param  array{name?: string, email?: string, tags?: list<string>}  $input
 * @return array{name: string, email: string, tags: list<string>}
 */
function normalizeRegistrationPayload(array $input): array
{
    $rawName = trim((string) ($input['name'] ?? ''));
    $name = (string) preg_replace('/\s+/', ' ', $rawName);
    $email = strtolower(trim((string) ($input['email'] ?? '')));

    $seen = [];
    $tags = [];
    foreach ($input['tags'] ?? [] as $tag) {
        $clean = strtolower(trim((string) $tag));
        if ($clean !== '' && ! isset($seen[$clean])) {
            $seen[$clean] = true;
            $tags[] = $clean;
        }
    }

    return [
        'name' => $name,
        'email' => $email,
        'tags' => $tags,
    ];
}
