<?php

use App\Models\Challenge;
use App\Models\GameSave;
use App\Models\User;
use App\Services\Sandbox\FakeChallengeRunner;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    FakeChallengeRunner::reset();
});

test('game_saves upserts slots 0..3 and validates zone, coordinates, facing, and slot bounds', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->postJson(route('game.saves.save', 0), [
            'current_zone' => 'foundations-hub',
            'player_x' => 12,
            'player_y' => 11,
            'facing' => 'up',
            'playtime_seconds' => 45,
        ])
        ->assertOk()
        ->assertJsonPath('save.slot', 0)
        ->assertJsonPath('save.currentZone', 'foundations-hub')
        ->assertJsonPath('save.playerX', 12)
        ->assertJsonPath('save.playerY', 11)
        ->assertJsonPath('save.facing', 'up')
        ->assertJsonPath('save.playtimeSeconds', 45);

    // Upserting same slot updates existing row cleanly without violating unique (user_id, slot)
    $this->actingAs($user)
        ->postJson(route('game.saves.save', 0), [
            'current_zone' => 'algorithm-woods',
            'player_x' => 6,
            'player_y' => 8,
            'facing' => 'right',
            'playtime_seconds' => 120,
        ])
        ->assertOk()
        ->assertJsonPath('save.currentZone', 'algorithm-woods')
        ->assertJsonPath('save.playerX', 6)
        ->assertJsonPath('save.playerY', 8);

    expect(GameSave::query()->where('user_id', $user->id)->where('slot', 0)->count())->toBe(1);

    // Invalid zone, facing, and slot bounds are rejected
    $this->actingAs($user)
        ->postJson(route('game.saves.save', 1), [
            'current_zone' => 'invalid-zone',
            'player_x' => -5,
            'player_y' => 999,
            'facing' => 'diagonal',
            'playtime_seconds' => -1,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['current_zone', 'player_x', 'player_y', 'facing', 'playtime_seconds']);

    $this->actingAs($user)
        ->postJson(route('game.saves.save', 4), [
            'current_zone' => 'artisan-city',
            'player_x' => 10,
            'player_y' => 10,
            'facing' => 'down',
            'playtime_seconds' => 10,
        ])
        ->assertStatus(422);
});

test('loading an older save slot restores world coordinates without rolling back authoritative coding progress', function () {
    Artisan::call('challenges:sync');
    $user = User::factory()->unverified()->create();

    // Save slot 1 early in Foundations Hub before solving any challenge
    $this->actingAs($user)
        ->postJson(route('game.saves.save', 1), [
            'current_zone' => 'foundations-hub',
            'player_x' => 12,
            'player_y' => 11,
            'facing' => 'down',
            'playtime_seconds' => 30,
            'label' => 'Early Hub Save',
        ])
        ->assertOk();

    // Later solve a challenge in Algorithm Woods
    $challenge = Challenge::query()->where('slug', 'first-duplicate-finder')->firstOrFail();

    $this->actingAs($user)
        ->postJson(route('challenges.submit', $challenge->slug), [
            'language' => 'php',
            'files' => $challenge->starter_files,
        ])
        ->assertOk()
        ->assertJsonPath('status', 'passed');

    // Load the earlier Slot 1 save: coordinates restore, but solved challenge & unlocked terminal stay intact!
    $loadResponse = $this->actingAs($user)
        ->postJson(route('game.saves.load', 1))
        ->assertOk()
        ->assertJsonPath('loaded.slot', 1)
        ->assertJsonPath('loaded.zone', 'foundations-hub')
        ->assertJsonPath('loaded.x', 12)
        ->assertJsonPath('loaded.y', 11)
        ->assertJsonPath('loaded.facing', 'down')
        ->assertJsonPath('state.summary.totalXp', 120)
        ->assertJsonPath('state.districts.algorithm-woods.completed', 1);

    expect($loadResponse->json('loaded.solvedSlugs'))->toContain('first-duplicate-finder')
        ->and($loadResponse->json('loaded.unlockedTerminals'))->toContain('algorithm-woods-bridge');
});

test('save slots enforce strict user ownership and forbid deleting auto-save slot 0', function () {
    $owner = User::factory()->unverified()->create();
    $otherUser = User::factory()->unverified()->create();

    $this->actingAs($owner)
        ->postJson(route('game.saves.save', 0), [
            'current_zone' => 'foundations-hub',
            'player_x' => 10,
            'player_y' => 10,
            'facing' => 'down',
            'playtime_seconds' => 20,
        ])
        ->assertOk();

    $this->actingAs($owner)
        ->postJson(route('game.saves.save', 2), [
            'current_zone' => 'legacy-harbor',
            'player_x' => 14,
            'player_y' => 9,
            'facing' => 'left',
            'playtime_seconds' => 200,
        ])
        ->assertOk();

    // Another user cannot see, load, or delete owner's slot 2
    $this->actingAs($otherUser)
        ->getJson(route('game.state'))
        ->assertOk()
        ->assertJsonPath('slots.2.exists', false);

    $this->actingAs($otherUser)
        ->postJson(route('game.saves.load', 2))
        ->assertNotFound();

    $this->actingAs($otherUser)
        ->deleteJson(route('game.saves.destroy', 2))
        ->assertNotFound();

    // Deleting Auto-Save slot 0 is rejected with 422
    $this->actingAs($owner)
        ->deleteJson(route('game.saves.destroy', 0))
        ->assertStatus(422);

    // Owner can delete manual slot 2
    $this->actingAs($owner)
        ->deleteJson(route('game.saves.destroy', 2))
        ->assertOk()
        ->assertJsonPath('deletedSlot', 2)
        ->assertJsonPath('state.slots.2.exists', false);

    expect(GameSave::query()->where('user_id', $owner->id)->where('slot', 2)->exists())->toBeFalse();
});

test('GET /api/game/state reports accurate district progress across all 23 synced challenges', function () {
    Artisan::call('challenges:sync');
    $user = User::factory()->unverified()->create();

    // Solve 1 Foundations + 1 Artisan City + 1 Legacy Harbor challenge
    foreach (['hello-elephpant', 'fix-welcome-route', 'off-by-one-date-timezone-bug'] as $slug) {
        $challenge = Challenge::query()->where('slug', $slug)->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('challenges.submit', $slug), [
                'language' => 'php',
                'files' => $challenge->starter_files,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'passed');
    }

    $this->actingAs($user)
        ->getJson(route('game.state'))
        ->assertOk()
        ->assertJsonPath('districts.foundations.completed', 1)
        ->assertJsonPath('districts.foundations.total', 8)
        ->assertJsonPath('districts.algorithm-woods.completed', 0)
        ->assertJsonPath('districts.algorithm-woods.total', 5)
        ->assertJsonPath('districts.artisan-city.completed', 1)
        ->assertJsonPath('districts.artisan-city.total', 5)
        ->assertJsonPath('districts.legacy-harbor.completed', 1)
        ->assertJsonPath('districts.legacy-harbor.total', 5)
        ->assertJsonPath('summary.completedChallenges', 3)
        ->assertJsonPath('summary.totalChallenges', 23);
});
