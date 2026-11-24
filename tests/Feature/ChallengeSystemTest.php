<?php

use App\Data\ChallengeExecutionRequest;
use App\Models\Challenge;
use App\Models\User;
use App\Services\Sandbox\DockerPhpChallengeRunner;
use App\Services\Sandbox\FakeChallengeRunner;
use App\Services\Sandbox\PistonChallengeRunner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    FakeChallengeRunner::reset();
});

test('challenges:sync imports all 23 bundles idempotently', function () {
    expect(Artisan::call('challenges:sync'))->toBe(0);
    $firstCount = Challenge::query()->count();
    expect($firstCount)->toBe(23);

    expect(Challenge::query()->where('discipline', 'foundations')->count())->toBe(8)
        ->and(Challenge::query()->where('discipline', 'programmer')->count())->toBe(5)
        ->and(Challenge::query()->where('discipline', 'developer')->count())->toBe(5)
        ->and(Challenge::query()->where('discipline', 'maintainer')->count())->toBe(5)
        ->and(Challenge::query()->where('district', 'algorithm-woods')->count())->toBe(5)
        ->and(Challenge::query()->where('district', 'artisan-city')->count())->toBe(5)
        ->and(Challenge::query()->where('district', 'legacy-harbor')->count())->toBe(5)
        ->and(Challenge::query()->whereNotNull('terminal_key')->count())->toBe(15);

    expect(Artisan::call('challenges:sync'))->toBe(0);
    expect(Challenge::query()->count())->toBe($firstCount);
});

test('challenge API payloads never leak hidden tests or decision answers and work without email verification', function () {
    Artisan::call('challenges:sync');
    $user = User::factory()->unverified()->create();

    $indexResponse = $this->actingAs($user)->getJson(route('challenges.index'));
    $indexResponse->assertOk()
        ->assertJsonCount(23, 'challenges')
        ->assertJsonPath('challenges.0.slug', 'hello-elephpant')
        ->assertJsonPath('challenges.0.order', 1)
        ->assertJsonPath('challenges.7.slug', 'your-first-bug-fix')
        ->assertJsonPath('challenges.7.order', 8);

    $indexBody = $indexResponse->getContent() ?: '';
    expect($indexBody)->not->toContain('hidden_tests')
        ->and($indexBody)->not->toContain('correct_decision_id');

    $showResponse = $this->actingAs($user)->getJson(
        route('challenges.show', 'unsafe-sql-query-audit'),
    );

    $showResponse->assertOk()
        ->assertJsonPath('challenge.slug', 'unsafe-sql-query-audit')
        ->assertJsonMissingPath('challenge.hidden_tests')
        ->assertJsonMissingPath('challenge.hiddenTests')
        ->assertJsonMissingPath('challenge.correct_decision_id')
        ->assertJsonMissingPath('challenge.correctDecisionId');

    $showBody = $showResponse->getContent() ?: '';
    expect($showBody)->not->toContain('keeps SQL injection payload strictly inside bindings');

    $foundationsShow = $this->actingAs($user)->getJson(
        route('challenges.show', 'potion-math-joining-text'),
    );

    $foundationsShow->assertOk();
    $descriptionHtml = (string) $foundationsShow->json('challenge.description_html');
    $hintsHtml = $foundationsShow->json('challenge.hints_html');

    expect($descriptionHtml)
        ->toContain('<h1>Step 3: Potion Math &amp; Joining Text</h1>')
        ->toContain('<blockquote>')
        ->toContain('Concept Reference:')
        ->toContain('<code>')
        ->not->toContain('## Requirements')
        ->and($hintsHtml)->toBeArray()->toHaveCount(3)
        ->and((string) $hintsHtml[0])->toContain('<code>');
});

test('submitting a passing solution awards XP, updates discipline rank, badges, titles, and hydrates dashboard live', function () {
    Artisan::call('challenges:sync');
    $user = User::factory()->unverified()->create();

    $challenge = Challenge::query()->where('slug', 'partial-payment-ledger')->firstOrFail();

    $submitResponse = $this->actingAs($user)->postJson(
        route('challenges.submit', $challenge->slug),
        [
            'language' => 'php',
            'files' => $challenge->starter_files,
        ],
    );

    $submitResponse->assertOk()
        ->assertJsonPath('status', 'passed')
        ->assertJsonPath('rewards.xpAwarded', 180)
        ->assertJsonPath('rewards.firstCompletion', true)
        ->assertJsonPath('rewards.disciplineRank', 'Apprentice')
        ->assertJsonPath('rewards.unlockedTriggerId', 'artisan-city-billing-guild');

    // Re-submitting the same solved challenge must not grant duplicate XP
    $repeatResponse = $this->actingAs($user)->postJson(
        route('challenges.submit', $challenge->slug),
        [
            'language' => 'php',
            'files' => $challenge->starter_files,
        ],
    );

    $repeatResponse->assertOk()
        ->assertJsonPath('status', 'passed')
        ->assertJsonPath('rewards.xpAwarded', 0)
        ->assertJsonPath('rewards.firstCompletion', false);

    // Dashboard hydrates live from database progression
    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('progress.summary.totalXp', 180)
            ->where('progress.summary.completedChallenges', 1)
            ->where('progress.paths.1.xp', 180)
            ->where('progress.paths.1.rank', 'Apprentice')
            ->where('progress.paths.1.completed', 1)
            ->where('progress.skills.1.proficiency', 25)
            ->where('progress.badges.3.unlocked', true)
            ->where('progress.titles.2.unlocked', true)
        );

    // Equip newly unlocked Artisan Builder title and verify live dashboard reflection
    $this->actingAs($user)
        ->post(route('titles.equip', 'artisan-builder'))
        ->assertRedirect();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('progress.summary.equippedTitle', 'Artisan Builder')
        );
});

test('technical decision challenges fail when the wrong architectural option is chosen', function () {
    Artisan::call('challenges:sync');
    $user = User::factory()->unverified()->create();
    $challenge = Challenge::query()->where('slug', 'unsafe-sql-query-audit')->firstOrFail();

    $this->actingAs($user)->postJson(
        route('challenges.submit', $challenge->slug),
        [
            'language' => 'php',
            'files' => $challenge->starter_files,
            'decision_choice' => 'string-replace',
        ],
    )->assertOk()
        ->assertJsonPath('status', 'failed')
        ->assertJsonPath('rewards.xpAwarded', 0);

    $this->actingAs($user)->postJson(
        route('challenges.submit', $challenge->slug),
        [
            'language' => 'php',
            'files' => $challenge->starter_files,
            'decision_choice' => 'parameterized-bindings',
        ],
    )->assertOk()
        ->assertJsonPath('status', 'passed')
        ->assertJsonPath('rewards.xpAwarded', 250);
});

test('sandbox timeout is handled cleanly without awarding XP', function () {
    Artisan::call('challenges:sync');
    FakeChallengeRunner::forceTimeout();

    $user = User::factory()->unverified()->create();
    $challenge = Challenge::query()->where('slug', 'fix-welcome-route')->firstOrFail();

    $this->actingAs($user)->postJson(
        route('challenges.submit', $challenge->slug),
        [
            'language' => 'php',
            'files' => $challenge->starter_files,
        ],
    )->assertOk()
        ->assertJsonPath('status', 'timeout')
        ->assertJsonPath('rewards.xpAwarded', 0);
});

test('piston sandbox runner dispatches isolated HTTP request and parses structured results and timeouts', function () {
    Http::fake([
        'http://127.0.0.1:2000/api/v2/execute' => Http::sequence()
            ->push([
                'run' => [
                    'stdout' => json_encode([
                        'results' => [
                            [
                                'name' => 'sample check',
                                'passed' => true,
                                'expected' => '200',
                                'actual' => '200',
                                'error' => null,
                            ],
                        ],
                    ]),
                    'stderr' => '',
                    'code' => 0,
                    'signal' => null,
                ],
            ], 200)
            ->push([
                'run' => [
                    'stdout' => '',
                    'stderr' => '',
                    'code' => 137,
                    'signal' => 'SIGKILL',
                ],
            ], 200),
    ]);

    $runner = new PistonChallengeRunner;

    $passResult = $runner->execute(new ChallengeExecutionRequest(
        challengeSlug: 'fix-welcome-route',
        language: 'php',
        files: ['WelcomeRoute.php' => '<?php'],
        testHarness: '<?php',
        scope: 'public',
    ));

    expect($passResult->status)->toBe('passed')
        ->and($passResult->passedCount())->toBe(1);

    $timeoutResult = $runner->execute(new ChallengeExecutionRequest(
        challengeSlug: 'fix-welcome-route',
        language: 'php',
        files: ['WelcomeRoute.php' => '<?php while (true) {}'],
        testHarness: '<?php',
        scope: 'public',
    ));

    expect($timeoutResult->status)->toBe('timeout')
        ->and($timeoutResult->errorMessage)->toContain('time limit');
});

test('all challenge reference solutions pass both public and hidden test suites', function () {
    $basePath = base_path('challenges');
    $checked = 0;

    foreach (File::directories($basePath) as $disciplineDir) {
        foreach (File::directories($disciplineDir) as $bundleDir) {
            $tmpDir = sys_get_temp_dir().'/elephpant_verify_'.uniqid('', true);
            File::makeDirectory($tmpDir, 0755, true);

            try {
                foreach (File::files($bundleDir.'/solution') as $solutionFile) {
                    File::copy($solutionFile->getPathname(), $tmpDir.'/'.$solutionFile->getFilename());
                }

                foreach (['public.php', 'hidden.php'] as $testFile) {
                    $targetTest = $tmpDir.'/'.$testFile;
                    File::copy($bundleDir.'/tests/'.$testFile, $targetTest);

                    $output = [];
                    $exitCode = 0;
                    exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg($targetTest), $output, $exitCode);

                    expect($exitCode)->toBe(0, "Non-zero exit in {$bundleDir}/tests/{$testFile}");

                    $json = json_decode(end($output) ?: '', true);
                    expect($json)->toBeArray()
                        ->and($json['results'])->not->toBeEmpty();

                    foreach ($json['results'] as $case) {
                        expect($case['passed'])->toBeTrue(
                            "Failed reference case [{$case['name']}] in {$bundleDir}/tests/{$testFile}",
                        );
                    }
                }

                $checked++;
            } finally {
                File::deleteDirectory($tmpDir);
            }
        }
    }

    expect($checked)->toBe(23);
});

test('completing all 8 foundations steps unlocks the Syntax Awakened badge and hydrates dashboard', function () {
    Artisan::call('challenges:sync');
    $user = User::factory()->unverified()->create();

    $foundations = Challenge::query()
        ->where('discipline', 'foundations')
        ->orderBy('sort_order')
        ->get();

    expect($foundations)->toHaveCount(8);

    $lastResponse = null;
    foreach ($foundations as $challenge) {
        $lastResponse = $this->actingAs($user)->postJson(
            route('challenges.submit', $challenge->slug),
            [
                'language' => 'php',
                'files' => $challenge->starter_files,
            ],
        )->assertOk()
            ->assertJsonPath('status', 'passed');
    }

    expect($lastResponse)->not->toBeNull();
    $lastResponse->assertJsonPath('rewards.unlockedBadges', ['syntax-awakened']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('progress.foundations.completed', 8)
            ->where('progress.foundations.total', 8)
            ->where('progress.foundations.badgeUnlocked', true)
            ->where('progress.badges.1.id', 'syntax-awakened')
            ->where('progress.badges.1.unlocked', true)
        );
});

test('solving a code-locked terminal challenge persists unlocked trigger across /play reloads', function () {
    Artisan::call('challenges:sync');
    $user = User::factory()->unverified()->create();
    $challenge = Challenge::query()->where('slug', 'first-duplicate-finder')->firstOrFail();

    $this->actingAs($user)->postJson(
        route('challenges.submit', $challenge->slug),
        [
            'language' => 'php',
            'files' => $challenge->starter_files,
        ],
    )->assertOk()
        ->assertJsonPath('status', 'passed')
        ->assertJsonPath('rewards.unlockedTriggerId', 'algorithm-woods-bridge');

    $this->actingAs($user)
        ->get(route('play'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Play')
            ->where('worldState.unlockedTriggers', ['algorithm-woods-bridge'])
            ->where('worldState.totalXp', 120)
            ->where('worldState.completedChallenges', 1)
        );
});

test('docker php sandbox runner invokes hardened ephemeral php 8.5 container and parses results', function () {
    Process::fake([
        '*' => Process::result(
            output: json_encode([
                'results' => [
                    [
                        'name' => 'docker isolated check',
                        'passed' => true,
                        'expected' => '200',
                        'actual' => '200',
                        'error' => null,
                    ],
                ],
            ]).PHP_EOL,
            errorOutput: '',
            exitCode: 0,
        ),
    ]);

    $runner = new DockerPhpChallengeRunner;

    $result = $runner->execute(new ChallengeExecutionRequest(
        challengeSlug: 'fix-welcome-route',
        language: 'php',
        files: ['WelcomeRoute.php' => '<?php'],
        testHarness: '<?php',
        scope: 'public',
    ));

    expect($result->status)->toBe('passed')
        ->and($result->passedCount())->toBe(1);

    Process::assertRan(function ($process): bool {
        $cmd = is_array($process->command) ? implode(' ', $process->command) : (string) $process->command;

        return str_contains($cmd, '--rm')
            && str_contains($cmd, '--network=none')
            && str_contains($cmd, '--read-only')
            && str_contains($cmd, '/tmp:rw,noexec,nosuid,size=16m')
            && str_contains($cmd, '--cap-drop=ALL')
            && str_contains($cmd, '--security-opt=no-new-privileges')
            && str_contains($cmd, '--memory=64m')
            && str_contains($cmd, '--memory-swap=64m')
            && str_contains($cmd, '--cpus=0.5')
            && str_contains($cmd, '--pids-limit=32')
            && str_contains($cmd, '--user 1000:1000')
            && str_contains($cmd, ':/workspace:ro')
            && str_contains($cmd, 'elephpant-php-sandbox:8.5');
    });
});

test('hidden test failures never leak hidden assertions, inputs, or expected values in submit responses', function () {
    Artisan::call('challenges:sync');
    $user = User::factory()->unverified()->create();
    $challenge = Challenge::query()->where('slug', 'potion-math-joining-text')->firstOrFail();

    config(['sandbox.driver' => 'docker']);

    Process::fake([
        '*' => Process::sequence()
            ->push(Process::result(
                output: json_encode([
                    'results' => [
                        [
                            'name' => 'calculates 3 potions at 25 coins each',
                            'passed' => true,
                            'expected' => '"3 potions cost 75 coins"',
                            'actual' => '"3 potions cost 75 coins"',
                            'error' => null,
                        ],
                    ],
                ]).PHP_EOL,
                exitCode: 0,
            ))
            ->push(Process::result(
                output: json_encode([
                    'results' => [
                        [
                            'name' => 'SECRET_HIDDEN_INPUT_12_POTIONS',
                            'passed' => false,
                            'expected' => '"12 potions cost 180 coins"',
                            'actual' => '"3 potions cost 75 coins"',
                            'error' => 'Receipt math or formatting did not match.',
                        ],
                    ],
                ]).PHP_EOL,
                exitCode: 0,
            )),
    ]);

    $response = $this->actingAs($user)->postJson(
        route('challenges.submit', $challenge->slug),
        [
            'language' => 'php',
            'files' => ['PotionShop.php' => '<?php function formatPotionReceipt(int $p, int $q): string { return "3 potions cost 75 coins"; }'],
        ],
    );

    $response->assertOk()
        ->assertJsonPath('status', 'failed')
        ->assertJsonPath('cases.1.name', 'Hidden Test #1')
        ->assertJsonPath('cases.1.passed', false)
        ->assertJsonPath('cases.1.expected', null)
        ->assertJsonPath('cases.1.actual', null)
        ->assertJsonPath('cases.1.scope', 'hidden');

    $body = $response->getContent() ?: '';
    expect($body)
        ->not->toContain('SECRET_HIDDEN_INPUT_12_POTIONS')
        ->not->toContain('12 potions cost 180 coins');
});

test('docker php sandbox runner terminates timeouts and surfaces disabled-function errors gracefully', function () {
    Process::fake([
        '*' => Process::sequence()
            ->push(Process::result(
                output: 'Fatal error: Maximum execution time of 3 seconds exceeded in /workspace/Loop.php on line 2',
                errorOutput: '',
                exitCode: 255,
            ))
            ->push(Process::result(
                output: 'Fatal error: Uncaught Error: Call to undefined function shell_exec() in /workspace/Exploit.php on line 2',
                errorOutput: '',
                exitCode: 255,
            )),
    ]);

    $runner = new DockerPhpChallengeRunner;

    $timeoutResult = $runner->execute(new ChallengeExecutionRequest(
        challengeSlug: 'hello-elephpant',
        language: 'php',
        files: ['Loop.php' => '<?php while (true) {}'],
        testHarness: '<?php require_once __DIR__."/Loop.php";',
        scope: 'public',
    ));

    expect($timeoutResult->status)->toBe('timeout')
        ->and($timeoutResult->errorMessage)->toContain('time limit');

    $blockedResult = $runner->execute(new ChallengeExecutionRequest(
        challengeSlug: 'hello-elephpant',
        language: 'php',
        files: ['Exploit.php' => '<?php shell_exec("id");'],
        testHarness: '<?php require_once __DIR__."/Exploit.php";',
        scope: 'public',
    ));

    expect($blockedResult->status)->toBe('error')
        ->and($blockedResult->errorMessage)->toContain('shell_exec');
});
