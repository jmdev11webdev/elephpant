<?php

use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameSaveController;
use App\Http\Controllers\PlayController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\TitleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::post('dashboard/titles/{titleKey}/equip', TitleController::class)->name('titles.equip');
    Route::get('play', PlayController::class)->name('play');
    Route::get('resources', ResourceController::class)->name('resources.index');

    Route::prefix('api/challenges')->name('challenges.')->group(function () {
        Route::get('/', [ChallengeController::class, 'index'])->name('index');
        Route::get('/{challenge:slug}', [ChallengeController::class, 'show'])->name('show');
        Route::post('/{challenge:slug}/run', [ChallengeController::class, 'run'])->name('run');
        Route::post('/{challenge:slug}/submit', [ChallengeController::class, 'submit'])->name('submit');
    });

    Route::prefix('api/game')->name('game.')->group(function () {
        Route::get('/state', [GameSaveController::class, 'state'])->name('state');
        Route::post('/saves/{slot}', [GameSaveController::class, 'save'])->name('saves.save');
        Route::post('/saves/{slot}/load', [GameSaveController::class, 'load'])->name('saves.load');
        Route::delete('/saves/{slot}', [GameSaveController::class, 'destroy'])->name('saves.destroy');
    });
});

require __DIR__.'/settings.php';
