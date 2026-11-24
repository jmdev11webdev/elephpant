<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class BuildSandboxImageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sandbox:build {--tag= : Optional Docker image tag override}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build and health-check the hardened ephemeral PHP 8.5+ Docker sandbox image.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tagOption = $this->option('tag');
        $image = is_string($tagOption) && $tagOption !== ''
            ? $tagOption
            : (string) config('sandbox.docker.image', 'elephpant-php-sandbox:8.5');

        $contextPath = base_path('docker/php-sandbox');

        $this->info("Building hardened sandbox image [{$image}] from {$contextPath}...");

        $build = Process::timeout(180)->run([
            'docker',
            'build',
            '-t',
            $image,
            $contextPath,
        ]);

        if (! $build->successful()) {
            $this->error('Docker build failed:');
            $this->line(trim($build->errorOutput() ?: $build->output()));

            return self::FAILURE;
        }

        $this->info("Verifying sandbox runtime [{$image}] with hardened container flags...");

        $check = Process::timeout(15)->run([
            'docker',
            'run',
            '--rm',
            '--network=none',
            '--read-only',
            '--tmpfs',
            '/tmp:rw,noexec,nosuid,size=16m',
            '--cap-drop=ALL',
            '--security-opt=no-new-privileges',
            '--memory=64m',
            '--memory-swap=64m',
            '--cpus=0.5',
            '--pids-limit=32',
            '--user',
            '1000:1000',
            $image,
            'php',
            '-v',
        ]);

        if (! $check->successful()) {
            $this->error('Sandbox healthcheck failed:');
            $this->line(trim($check->errorOutput() ?: $check->output()));

            return self::FAILURE;
        }

        $versionLine = strtok(trim($check->output()), "\n") ?: '';
        $this->info("Sandbox ready: {$versionLine}");

        return self::SUCCESS;
    }
}
