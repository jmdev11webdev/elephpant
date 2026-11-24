<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard with progress overview props', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('progress.summary.equippedTitle', 'Localhost Apprentice')
            ->where('progress.summary.totalXp', 0)
            ->where('progress.foundations.completed', 0)
            ->where('progress.foundations.total', 8)
            ->where('progress.foundations.badgeUnlocked', false)
            ->has('progress.paths', 3)
            ->has('progress.skills', 5)
            ->has('progress.badges', 6)
            ->has('progress.titles', 5)
        );
});

test('unverified users can visit the dashboard when email verification is disabled', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('guests are redirected to the login page when visiting play', function () {
    $this->get(route('play'))
        ->assertRedirect(route('login'));
});

test('authenticated unverified users can visit the dedicated play page', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('play'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Play')
        );
});

test('passkey login options endpoint returns valid webauthn challenge', function () {
    $response = $this->getJson('/passkeys/login/options');

    $response->assertOk()
        ->assertJsonStructure(['options' => ['challenge', 'rpId']])
        ->assertJsonPath('options.rpId', 'localhost');
});
