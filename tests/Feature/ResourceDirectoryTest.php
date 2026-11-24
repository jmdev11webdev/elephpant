<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page when visiting resources', function () {
    $this->get(route('resources.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view the curated resource directory without email verification', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('resources.index'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Resources')
            ->has('categories', 5)
            ->where('categories', [
                'PHP Core & Packages',
                'Laravel Core & News',
                'Courses & Tutorials',
                'Laravel + Livewire Stack',
                'Laravel + Inertia Frontend Stacks (Vue, React, Svelte)',
            ])
            ->has('resources', 19)
            ->has('resources.0', fn (Assert $item) => $item
                ->hasAll(['title', 'url', 'category', 'description'])
            )
        );
});

test('resource catalog includes all required canonical sources', function () {
    $urls = collect(config('resources.items'))->pluck('url')->all();

    expect($urls)->toEqualCanonicalizing([
        'https://www.php.net',
        'https://phpnews.net',
        'https://phptherightway.com',
        'https://php.watch',
        'https://getcomposer.org',
        'https://packagist.org',
        'https://laravel.com',
        'https://laravel-news.com',
        'https://laravel.com/starter-kits',
        'https://laracasts.com',
        'https://laraveldaily.com',
        'https://livewire.laravel.com',
        'https://alpinejs.dev',
        'https://fluxui.dev',
        'https://tailwindcss.com',
        'https://inertiajs.com',
        'https://vuejs.org',
        'https://react.dev',
        'https://svelte.dev',
    ]);
});
