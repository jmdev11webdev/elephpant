<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Curated Ecosystem Resource Categories
    |--------------------------------------------------------------------------
    |
    | Ordered list of categories displayed in the out-of-game Resource
    | Directory. External links are strictly curated static references.
    |
    */

    'categories' => [
        'PHP Core & Packages',
        'Laravel Core & News',
        'Courses & Tutorials',
        'Laravel + Livewire Stack',
        'Laravel + Inertia Frontend Stacks (Vue, React, Svelte)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Curated Ecosystem Resources
    |--------------------------------------------------------------------------
    |
    | Static catalog of canonical PHP and Laravel ecosystem websites. No
    | scraping, RSS fetching, or embedded iframes are used.
    |
    */

    'items' => [
        // PHP Core & Packages
        [
            'title' => 'PHP.net',
            'url' => 'https://www.php.net',
            'category' => 'PHP Core & Packages',
            'description' => 'Official PHP language documentation, function reference, release notes, and downloads.',
        ],
        [
            'title' => 'PHP News',
            'url' => 'https://phpnews.net',
            'category' => 'PHP Core & Packages',
            'description' => 'Curated news, articles, and community highlights from across the PHP ecosystem.',
        ],
        [
            'title' => 'PHP: The Right Way',
            'url' => 'https://phptherightway.com',
            'category' => 'PHP Core & Packages',
            'description' => 'Community-maintained reference covering modern PHP coding standards, security, and best practices.',
        ],
        [
            'title' => 'PHP.Watch',
            'url' => 'https://php.watch',
            'category' => 'PHP Core & Packages',
            'description' => 'In-depth guides on new PHP versions, RFCs, deprecations, and upgrade changes.',
        ],
        [
            'title' => 'Composer',
            'url' => 'https://getcomposer.org',
            'category' => 'PHP Core & Packages',
            'description' => 'The standard dependency manager for PHP projects, autoloading, and package scripts.',
        ],
        [
            'title' => 'Packagist',
            'url' => 'https://packagist.org',
            'category' => 'PHP Core & Packages',
            'description' => 'The primary public package repository for discovering and publishing Composer libraries.',
        ],

        // Laravel Core & News
        [
            'title' => 'Laravel',
            'url' => 'https://laravel.com',
            'category' => 'Laravel Core & News',
            'description' => 'Official Laravel framework website, documentation, and first-party ecosystem tools.',
        ],
        [
            'title' => 'Laravel News',
            'url' => 'https://laravel-news.com',
            'category' => 'Laravel Core & News',
            'description' => 'Weekly Laravel releases, tutorials, packages, podcasts, and community updates.',
        ],
        [
            'title' => 'Laravel Starter Kits',
            'url' => 'https://laravel.com/starter-kits',
            'category' => 'Laravel Core & News',
            'description' => 'Official application scaffolds for Laravel with Vue, React, Livewire, and authentication built in.',
        ],

        // Courses & Tutorials
        [
            'title' => 'Laracasts',
            'url' => 'https://laracasts.com',
            'category' => 'Courses & Tutorials',
            'description' => 'Structured video series covering PHP, Laravel, Vue, testing, and software architecture.',
        ],
        [
            'title' => 'Laravel Daily',
            'url' => 'https://laraveldaily.com',
            'category' => 'Courses & Tutorials',
            'description' => 'Practical Laravel tutorials, tips, real-world patterns, and project-focused courses.',
        ],

        // Laravel + Livewire Stack
        [
            'title' => 'Livewire',
            'url' => 'https://livewire.laravel.com',
            'category' => 'Laravel + Livewire Stack',
            'description' => 'Full-stack framework for building dynamic Laravel interfaces directly in PHP and Blade.',
        ],
        [
            'title' => 'Alpine.js',
            'url' => 'https://alpinejs.dev',
            'category' => 'Laravel + Livewire Stack',
            'description' => 'Lightweight declarative JavaScript framework for sprinkling interactivity into HTML markup.',
        ],
        [
            'title' => 'Flux UI',
            'url' => 'https://fluxui.dev',
            'category' => 'Laravel + Livewire Stack',
            'description' => 'Official UI component library crafted specifically for Laravel Livewire applications.',
        ],
        [
            'title' => 'Tailwind CSS',
            'url' => 'https://tailwindcss.com',
            'category' => 'Laravel + Livewire Stack',
            'description' => 'Utility-first CSS framework for building responsive, custom-themed web interfaces rapidly.',
        ],

        // Laravel + Inertia Frontend Stacks (Vue, React, Svelte)
        [
            'title' => 'Inertia.js',
            'url' => 'https://inertiajs.com',
            'category' => 'Laravel + Inertia Frontend Stacks (Vue, React, Svelte)',
            'description' => 'Modern monolith bridge connecting Laravel controllers directly to Vue, React, or Svelte SPAs.',
        ],
        [
            'title' => 'Vue.js',
            'url' => 'https://vuejs.org',
            'category' => 'Laravel + Inertia Frontend Stacks (Vue, React, Svelte)',
            'description' => 'Progressive JavaScript framework for approachable, reactive single-file component UIs.',
        ],
        [
            'title' => 'React',
            'url' => 'https://react.dev',
            'category' => 'Laravel + Inertia Frontend Stacks (Vue, React, Svelte)',
            'description' => 'Component-based JavaScript library for building interactive web and native user interfaces.',
        ],
        [
            'title' => 'Svelte',
            'url' => 'https://svelte.dev',
            'category' => 'Laravel + Inertia Frontend Stacks (Vue, React, Svelte)',
            'description' => 'Compiler-driven frontend framework that turns declarative components into lean JavaScript.',
        ],
    ],

];
