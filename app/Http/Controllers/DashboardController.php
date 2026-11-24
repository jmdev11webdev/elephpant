<?php

namespace App\Http\Controllers;

use App\Services\PlayerProgressOverview;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly PlayerProgressOverview $progressOverview,
    ) {}

    /**
     * Display the developer progress overview hub.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'progress' => $this->progressOverview->forUser($request->user()),
        ]);
    }
}
