<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Challenge Sandbox Driver
    |--------------------------------------------------------------------------
    |
    | Supported drivers:
    | - "docker": Hardened, ephemeral elephpant-php-sandbox:8.5 containers (default)
    | - "piston": Remote or isolated Piston-compatible sandbox API worker
    | - "fake": Deterministic driver for automated PHPUnit/Pest tests
    |
    */

    'driver' => env('SANDBOX_DRIVER', 'docker'),

    /*
    |--------------------------------------------------------------------------
    | Hardened Ephemeral Docker PHP 8.5+ Sandbox Configuration
    |--------------------------------------------------------------------------
    */

    'docker' => [
        'image' => (string) env('SANDBOX_DOCKER_IMAGE', 'elephpant-php-sandbox:8.5'),
        'run_timeout_ms' => (int) env('SANDBOX_RUN_TIMEOUT_MS', 5000),
        'memory' => (string) env('SANDBOX_DOCKER_MEMORY', '64m'),
        'memory_swap' => (string) env('SANDBOX_DOCKER_MEMORY_SWAP', '64m'),
        'cpus' => (string) env('SANDBOX_DOCKER_CPUS', '0.5'),
        'pids_limit' => (string) env('SANDBOX_DOCKER_PIDS_LIMIT', '32'),
        'user' => (string) env('SANDBOX_DOCKER_USER', '1000:1000'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Remote / Hosted Piston-Compatible Sandbox Configuration
    |--------------------------------------------------------------------------
    */

    'piston' => [
        'base_url' => rtrim((string) env('SANDBOX_PISTON_URL', 'http://127.0.0.1:2000'), '/'),
        'api_key' => env('SANDBOX_PISTON_API_KEY'),
        'http_timeout_seconds' => (int) env('SANDBOX_HTTP_TIMEOUT', 10),
        'run_timeout_ms' => (int) env('SANDBOX_RUN_TIMEOUT_MS', 5000),
        'compile_timeout_ms' => (int) env('SANDBOX_COMPILE_TIMEOUT_MS', 5000),
        'memory_limit_bytes' => (int) env('SANDBOX_MEMORY_LIMIT_BYTES', 67108864),
        'runtimes' => [
            'php' => (string) env('SANDBOX_PHP_VERSION', '8.5.13'),
            'typescript' => (string) env('SANDBOX_TS_VERSION', '5.0.3'),
            'sql' => (string) env('SANDBOX_SQL_VERSION', '3.36.0'),
        ],
    ],

];
