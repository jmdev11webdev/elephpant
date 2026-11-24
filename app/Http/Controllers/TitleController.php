<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserTitle;
use App\Services\ProgressionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TitleController extends Controller
{
    public function __construct(
        private readonly ProgressionService $progression,
    ) {}

    /**
     * Equip an unlocked developer title for the authenticated player.
     */
    public function __invoke(Request $request, string $titleKey): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->progression->ensureStarterUnlocks($user);

        $targetTitle = UserTitle::query()
            ->where('user_id', $user->id)
            ->where('title_key', $titleKey)
            ->first();

        if ($targetTitle === null) {
            return back()->withErrors([
                'title' => 'That title has not been unlocked yet.',
            ]);
        }

        UserTitle::query()
            ->where('user_id', $user->id)
            ->update(['equipped' => false]);

        $targetTitle->equipped = true;
        $targetTitle->save();

        return back();
    }
}
