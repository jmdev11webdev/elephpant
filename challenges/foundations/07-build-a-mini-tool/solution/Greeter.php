<?php

declare(strict_types=1);

function greet(string $name): string
{
    $cleanName = trim($name);

    return "Hello, {$cleanName}!";
}
