<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use JsonException;

class SyncChallengesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenges:sync {--path= : Optional path to the challenges directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync version-controlled challenge bundles from challenges/<discipline>/<slug>/ into the database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $customPath = $this->option('path');
        $basePath = is_string($customPath) && $customPath !== ''
            ? $customPath
            : base_path('challenges');

        if (! File::isDirectory($basePath)) {
            $this->error("Challenges directory not found at [{$basePath}].");

            return self::FAILURE;
        }

        $synced = 0;

        foreach (File::directories($basePath) as $disciplineDir) {
            foreach (File::directories($disciplineDir) as $bundleDir) {
                $metaPath = $bundleDir.'/meta.json';
                $promptPath = $bundleDir.'/prompt.md';
                $publicTestPath = $bundleDir.'/tests/public.php';
                $hiddenTestPath = $bundleDir.'/tests/hidden.php';
                $starterDir = $bundleDir.'/starter';

                if (
                    ! File::exists($metaPath)
                    || ! File::exists($promptPath)
                    || ! File::exists($publicTestPath)
                    || ! File::exists($hiddenTestPath)
                    || ! File::isDirectory($starterDir)
                ) {
                    $this->warn("Skipping incomplete challenge bundle: {$bundleDir}");

                    continue;
                }

                try {
                    /** @var array<string, mixed> $meta */
                    $meta = json_decode(
                        File::get($metaPath),
                        true,
                        512,
                        JSON_THROW_ON_ERROR,
                    );
                } catch (JsonException $exception) {
                    $this->error("Invalid meta.json in {$bundleDir}: {$exception->getMessage()}");

                    return self::FAILURE;
                }

                $starterFiles = [];
                foreach (File::files($starterDir) as $starterFile) {
                    $starterFiles[$starterFile->getFilename()] = File::get($starterFile->getPathname());
                }
                ksort($starterFiles);

                $slug = is_string($meta['slug'] ?? null)
                    ? $meta['slug']
                    : basename($bundleDir);

                $discipline = (string) ($meta['discipline'] ?? basename($disciplineDir));
                $defaultDistrict = match ($discipline) {
                    'foundations' => 'localhost-academy',
                    'programmer' => 'algorithm-woods',
                    'developer' => 'artisan-city',
                    'maintainer' => 'legacy-harbor',
                    default => null,
                };
                $mapTriggerId = isset($meta['mapTriggerId']) ? (string) $meta['mapTriggerId'] : null;
                $terminalKey = isset($meta['terminalKey'])
                    ? (string) $meta['terminalKey']
                    : $mapTriggerId;

                Challenge::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => (string) ($meta['title'] ?? $slug),
                        'discipline' => $discipline,
                        'district' => isset($meta['district']) ? (string) $meta['district'] : $defaultDistrict,
                        'category' => (string) ($meta['category'] ?? 'General'),
                        'difficulty' => (string) ($meta['difficulty'] ?? 'beginner'),
                        'sort_order' => isset($meta['order']) ? (int) $meta['order'] : null,
                        'evaluation_type' => (string) ($meta['evaluationType'] ?? 'code_execution'),
                        'supported_languages' => is_array($meta['supportedLanguages'] ?? null)
                            ? array_values($meta['supportedLanguages'])
                            : ['php'],
                        'xp_reward' => (int) ($meta['xpReward'] ?? 100),
                        'estimated_minutes' => (int) ($meta['estimatedMinutes'] ?? 10),
                        'skill_key' => (string) ($meta['skillKey'] ?? 'backend-apis'),
                        'map_trigger_id' => $mapTriggerId,
                        'terminal_key' => $terminalKey,
                        'prompt_markdown' => File::get($promptPath),
                        'starter_files' => $starterFiles,
                        'hints' => is_array($meta['hints'] ?? null)
                            ? array_values($meta['hints'])
                            : [],
                        'decision_options' => is_array($meta['decisionOptions'] ?? null)
                            ? array_values($meta['decisionOptions'])
                            : null,
                        'correct_decision_id' => isset($meta['correctDecisionId'])
                            ? (string) $meta['correctDecisionId']
                            : null,
                        'public_tests' => File::get($publicTestPath),
                        'hidden_tests' => File::get($hiddenTestPath),
                    ],
                );

                $synced++;
            }
        }

        $this->info("Synced {$synced} challenge bundle(s) into the database.");

        return self::SUCCESS;
    }
}
