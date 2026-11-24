<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use App\Models\GameSave;
use App\Models\User;
use App\Models\UserWorldTrigger;

class GameStateService
{
    public function __construct(
        private readonly PlayerProgressOverview $progressOverview,
    ) {}

    /**
     * Return full authoritative game state for the authenticated user.
     *
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $progress = $this->progressOverview->forUser($user);

        /** @var list<int> $solvedChallengeIds */
        $solvedChallengeIds = ChallengeSubmission::query()
            ->where('user_id', $user->id)
            ->where('mode', 'submit')
            ->where('status', 'passed')
            ->distinct()
            ->pluck('challenge_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $solvedIdLookup = array_flip($solvedChallengeIds);

        $allChallenges = Challenge::query()
            ->orderByRaw('COALESCE(sort_order, 9999)')
            ->orderBy('title')
            ->get(['id', 'slug', 'discipline', 'district', 'terminal_key', 'map_trigger_id']);

        $solvedSlugs = [];
        $unlockedTerminalMap = [];

        $districtStats = [
            'foundations' => ['completed' => 0, 'total' => 0],
            'algorithm-woods' => ['completed' => 0, 'total' => 0],
            'artisan-city' => ['completed' => 0, 'total' => 0],
            'legacy-harbor' => ['completed' => 0, 'total' => 0],
        ];

        foreach ($allChallenges as $challenge) {
            $districtKey = $challenge->discipline === 'foundations'
                ? 'foundations'
                : ($challenge->district ?? match ($challenge->discipline) {
                    'programmer' => 'algorithm-woods',
                    'developer' => 'artisan-city',
                    'maintainer' => 'legacy-harbor',
                    default => 'foundations',
                });

            if (isset($districtStats[$districtKey])) {
                $districtStats[$districtKey]['total']++;
            }

            if (isset($solvedIdLookup[$challenge->id])) {
                $solvedSlugs[] = $challenge->slug;

                if (isset($districtStats[$districtKey])) {
                    $districtStats[$districtKey]['completed']++;
                }

                if ($challenge->terminal_key !== null && $challenge->terminal_key !== '') {
                    $unlockedTerminalMap[$challenge->terminal_key] = true;
                }

                if ($challenge->map_trigger_id !== null && $challenge->map_trigger_id !== '') {
                    $unlockedTerminalMap[$challenge->map_trigger_id] = true;
                }
            }
        }

        $storedTriggers = UserWorldTrigger::query()
            ->where('user_id', $user->id)
            ->pluck('trigger_id');

        foreach ($storedTriggers as $triggerId) {
            if (is_string($triggerId) && $triggerId !== '') {
                $unlockedTerminalMap[$triggerId] = true;
            }
        }

        $unlockedTerminals = array_keys($unlockedTerminalMap);

        $savesBySlot = GameSave::query()
            ->where('user_id', $user->id)
            ->orderBy('slot')
            ->get()
            ->keyBy('slot');

        $slots = [];
        for ($slot = 0; $slot <= 3; $slot++) {
            /** @var GameSave|null $record */
            $record = $savesBySlot->get($slot);
            $slots[] = $record !== null
                ? $this->formatSaveSlot($record)
                : [
                    'slot' => $slot,
                    'isAutoSave' => $slot === 0,
                    'exists' => false,
                    'label' => $slot === 0 ? 'Auto-Save' : "Slot {$slot}",
                    'currentZone' => null,
                    'zoneName' => null,
                    'playerX' => null,
                    'playerY' => null,
                    'facing' => null,
                    'playtimeSeconds' => 0,
                    'savedAt' => null,
                    'savedAtHuman' => null,
                ];
        }

        /** @var GameSave|null $latestSave */
        $latestSave = GameSave::query()
            ->where('user_id', $user->id)
            ->orderByDesc('saved_at')
            ->orderByDesc('updated_at')
            ->first();

        $resume = $latestSave !== null
            ? [
                'zone' => $this->normalizeZone($latestSave->current_zone),
                'x' => $latestSave->player_x,
                'y' => $latestSave->player_y,
                'facing' => $latestSave->facing,
                'playtimeSeconds' => $latestSave->playtime_seconds,
                'fromSlot' => $latestSave->slot,
            ]
            : [
                'zone' => 'foundations-hub',
                'x' => 24,
                'y' => 19,
                'facing' => 'down',
                'playtimeSeconds' => 0,
                'fromSlot' => null,
            ];

        return [
            'resume' => $resume,
            'slots' => $slots,
            'districts' => $districtStats,
            'solvedSlugs' => $solvedSlugs,
            'unlockedTerminals' => $unlockedTerminals,
            'summary' => [
                'equippedTitle' => $progress['summary']['equippedTitle'],
                'level' => $progress['summary']['level'],
                'totalXp' => $progress['summary']['totalXp'],
                'completedChallenges' => $progress['summary']['completedChallenges'],
                'totalChallenges' => $progress['summary']['totalChallenges'],
            ],
        ];
    }

    /**
     * Format a single persisted GameSave record for JSON responses.
     *
     * @return array<string, mixed>
     */
    public function formatSaveSlot(GameSave $save): array
    {
        $zone = $this->normalizeZone($save->current_zone);

        return [
            'slot' => $save->slot,
            'isAutoSave' => $save->slot === 0,
            'exists' => true,
            'label' => $save->label ?? ($save->slot === 0 ? 'Auto-Save' : "Slot {$save->slot}"),
            'currentZone' => $zone,
            'zoneName' => $this->zoneDisplayName($zone),
            'playerX' => $save->player_x,
            'playerY' => $save->player_y,
            'facing' => $save->facing,
            'playtimeSeconds' => $save->playtime_seconds,
            'savedAt' => $save->saved_at?->toIso8601String(),
            'savedAtHuman' => $save->saved_at?->diffForHumans(),
        ];
    }

    public function normalizeZone(string $zone): string
    {
        return $zone === 'localhost-town' ? 'foundations-hub' : $zone;
    }

    public function zoneDisplayName(string $zone): string
    {
        return match ($this->normalizeZone($zone)) {
            'foundations-hub' => 'Foundations Hub',
            'algorithm-woods' => 'Algorithm Woods',
            'artisan-city' => 'Artisan City',
            'legacy-harbor' => 'Legacy Harbor',
            'challenge-center' => 'Challenge Center',
            'starter-house' => 'Localhost Academy',
            'logic-pavilion' => 'Logic Pavilion',
            'billing-guild' => 'Billing Guild',
            'security-warehouse' => 'Security Warehouse',
            default => 'Foundations Hub',
        };
    }
}
