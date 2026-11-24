<?php

declare(strict_types=1);

function hasBalancedDelimiters(string $expression): bool
{
    $pairs = [')' => '(', ']' => '[', '}' => '{'];
    $openers = ['(' => true, '[' => true, '{' => true];
    $stack = [];
    $len = strlen($expression);

    for ($i = 0; $i < $len; $i++) {
        $ch = $expression[$i];
        if (isset($openers[$ch])) {
            $stack[] = $ch;
        } elseif (isset($pairs[$ch])) {
            if ($stack === [] || array_pop($stack) !== $pairs[$ch]) {
                return false;
            }
        }
    }

    return $stack === [];
}
