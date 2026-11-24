<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GameStateService;
use App\Services\PlayerProgressOverview;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlayController extends Controller
{
    public function __construct(
        private readonly PlayerProgressOverview $progressOverview,
        private readonly GameStateService $gameState,
    ) {}

    /**
     * Render the dedicated /play game viewport hydrated with persistent world unlocks,
     * authoritative save state, and live discipline progression.
     */
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $progress = $this->progressOverview->forUser($user);
        $state = $this->gameState->forUser($user);

        $disciplineRanks = [];
        foreach ($progress['paths'] as $path) {
            $disciplineRanks[$path['id']] = [
                'rank' => $path['rank'],
                'xp' => $path['xp'],
                'completed' => $path['completed'],
                'total' => $path['total'],
            ];
        }

        $unlockedBadges = array_values(array_map(
            static fn (array $badge): string => $badge['name'],
            array_filter($progress['badges'], static fn (array $badge): bool => $badge['unlocked']),
        ));

        return Inertia::render('Play', [
            'worldState' => [
                'unlockedTriggers' => $state['unlockedTerminals'],
                'equippedTitle' => $progress['summary']['equippedTitle'],
                'level' => $progress['summary']['level'],
                'totalXp' => $progress['summary']['totalXp'],
                'completedChallenges' => $progress['summary']['completedChallenges'],
                'totalChallenges' => $progress['summary']['totalChallenges'],
                'disciplineRanks' => $disciplineRanks,
                'unlockedBadges' => $unlockedBadges,
                'resume' => $state['resume'],
            ],
            'initialGameState' => $state,
        ]);
    }
}
