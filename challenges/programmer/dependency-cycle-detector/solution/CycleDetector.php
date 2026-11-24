<?php

declare(strict_types=1);

/**
 * @param  array<string, list<string>>  $graph
 */
function hasDependencyCycle(array $graph): bool
{
    $state = [];

    $dfs = static function (string $node) use (&$dfs, &$state, $graph): bool {
        $state[$node] = 1;

        foreach ($graph[$node] ?? [] as $dep) {
            $depState = $state[$dep] ?? 0;
            if ($depState === 1) {
                return true;
            }
            if ($depState === 0 && $dfs($dep)) {
                return true;
            }
        }

        $state[$node] = 2;

        return false;
    };

    foreach (array_keys($graph) as $node) {
        if (($state[$node] ?? 0) === 0 && $dfs($node)) {
            return true;
        }
    }

    return false;
}
