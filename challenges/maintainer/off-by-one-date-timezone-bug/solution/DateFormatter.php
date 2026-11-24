<?php

declare(strict_types=1);

function formatLocalDate(string $utcIsoTimestamp, string $targetTimezone): string
{
    $dt = new DateTimeImmutable($utcIsoTimestamp, new DateTimeZone('UTC'));

    return $dt->setTimezone(new DateTimeZone($targetTimezone))->format('Y-m-d');
}
