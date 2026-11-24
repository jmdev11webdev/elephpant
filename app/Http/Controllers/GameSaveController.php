<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveGameSlotRequest;
use App\Models\GameSave;
use App\Models\User;
use App\Services\GameStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameSaveController extends Controller
{
    public function __construct(
        private readonly GameStateService $gameState,
    ) {}

    /**
     * Return active resume state, all save slots (0..3), district stats,
     * solved challenge slugs, and authoritative unlocked terminal keys.
     */
    public function state(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json($this->gameState->forUser($user));
    }

    /**
     * Upsert save slot 0..3 with the player's current world coordinates & playtime.
     */
    public function save(SaveGameSlotRequest $request, int $slot): JsonResponse
    {
        if ($slot < 0 || $slot > 3) {
            return response()->json([
                'message' => 'Save slot must be between 0 (Auto-Save) and 3.',
            ], 422);
        }

        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $zone = $this->gameState->normalizeZone((string) $validated['current_zone']);
        $label = isset($validated['label']) && is_string($validated['label']) && trim($validated['label']) !== ''
            ? trim($validated['label'])
            : ($slot === 0 ? 'Auto-Save' : "Slot {$slot}");

        $save = GameSave::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'slot' => $slot,
            ],
            [
                'label' => $label,
                'current_zone' => $zone,
                'player_x' => (int) $validated['player_x'],
                'player_y' => (int) $validated['player_y'],
                'facing' => (string) $validated['facing'],
                'playtime_seconds' => (int) $validated['playtime_seconds'],
                'saved_at' => now(),
            ],
        );

        return response()->json([
            'save' => $this->gameState->formatSaveSlot($save),
            'state' => $this->gameState->forUser($user),
        ]);
    }

    /**
     * Load a save slot's world coordinates merged with authoritative unlocked terminals & districts.
     */
    public function load(Request $request, int $slot): JsonResponse
    {
        if ($slot < 0 || $slot > 3) {
            return response()->json([
                'message' => 'Save slot must be between 0 and 3.',
            ], 422);
        }

        /** @var User $user */
        $user = $request->user();

        $save = GameSave::query()
            ->where('user_id', $user->id)
            ->where('slot', $slot)
            ->first();

        if ($save === null) {
            return response()->json([
                'message' => "Save slot {$slot} is empty.",
            ], 404);
        }

        $authoritative = $this->gameState->forUser($user);

        return response()->json([
            'loaded' => [
                'slot' => $save->slot,
                'zone' => $this->gameState->normalizeZone($save->current_zone),
                'x' => $save->player_x,
                'y' => $save->player_y,
                'facing' => $save->facing,
                'playtimeSeconds' => $save->playtime_seconds,
                'unlockedTerminals' => $authoritative['unlockedTerminals'],
                'solvedSlugs' => $authoritative['solvedSlugs'],
                'districts' => $authoritative['districts'],
            ],
            'state' => $authoritative,
        ]);
    }

    /**
     * Delete a manual save slot (1..3). Reject deleting slot 0 (Auto-Save).
     */
    public function destroy(Request $request, int $slot): JsonResponse
    {
        if ($slot === 0) {
            return response()->json([
                'message' => 'Auto-Save (slot 0) cannot be deleted.',
            ], 422);
        }

        if ($slot < 1 || $slot > 3) {
            return response()->json([
                'message' => 'Manual save slot must be between 1 and 3.',
            ], 422);
        }

        /** @var User $user */
        $user = $request->user();

        $deleted = GameSave::query()
            ->where('user_id', $user->id)
            ->where('slot', $slot)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'message' => "Save slot {$slot} is already empty.",
            ], 404);
        }

        return response()->json([
            'deletedSlot' => $slot,
            'state' => $this->gameState->forUser($user),
        ]);
    }
}
