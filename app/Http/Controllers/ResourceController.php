<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ResourceController extends Controller
{
    /**
     * Display the curated out-of-game PHP and Laravel resource directory.
     */
    public function __invoke(): Response
    {
        /** @var array<int, string> $categories */
        $categories = config('resources.categories', []);

        /** @var array<int, array{title: string, url: string, category: string, description: string}> $resources */
        $resources = config('resources.items', []);

        return Inertia::render('Resources', [
            'categories' => $categories,
            'resources' => $resources,
        ]);
    }
}
