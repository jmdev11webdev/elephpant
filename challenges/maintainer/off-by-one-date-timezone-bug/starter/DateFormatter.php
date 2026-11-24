<?php

declare(strict_types=1);

function formatLocalDate(string $utcIsoTimestamp, string $targetTimezone): string
{
    // BUG: Ignores target timezone and subtracts 1 day unconditionally.
    $dt = new DateTimeImmutable($utcIsoTimestamp, new DateTimeZone('UTC'));

    return $dt->modify('-1 day')->format('Y-m-d');
}
