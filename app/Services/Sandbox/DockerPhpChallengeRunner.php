<?php

namespace App\Services\Sandbox;

use App\Contracts\ChallengeRunnerInterface;
use App\Data\ChallengeExecutionRequest;
use App\Data\ChallengeExecutionResult;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use JsonException;

class DockerPhpChallengeRunner implements ChallengeRunnerInterface
{
    public function execute(ChallengeExecutionRequest $request): ChallengeExecutionResult
    {
        $image = (string) config('sandbox.docker.image', 'elephpant-php-sandbox:8.5');
        $timeoutSeconds = max(1, (int) ceil(((int) config('sandbox.docker.run_timeout_ms', 5000)) / 1000));
        $memoryLimit = (string) config('sandbox.docker.memory', '64m');
        $memorySwap = (string) config('sandbox.docker.memory_swap', '64m');
        $cpus = (string) config('sandbox.docker.cpus', '0.5');
        $pidsLimit = (string) config('sandbox.docker.pids_limit', '32');
        $user = (string) config('sandbox.docker.user', '1000:1000');

        $workspaceDir = sys_get_temp_dir().'/elephpant_sandbox_'.bin2hex(random_bytes(8));
        File::makeDirectory($workspaceDir, 0755, true);

        $startedAt = hrtime(true);

        try {
            File::put($workspaceDir.'/run_tests.php', $request->testHarness);
            @chmod($workspaceDir.'/run_tests.php', 0644);

            foreach ($request->files as $filename => $content) {
                $safeName = basename($filename);

                if ($safeName === '' || $safeName === 'run_tests.php') {
                    continue;
                }

                $targetPath = $workspaceDir.'/'.$safeName;
                File::put($targetPath, $content);
                @chmod($targetPath, 0644);
            }

            $command = [
                'docker',
                'run',
                '--rm',
                '--network=none',
                '--read-only',
                '--tmpfs',
                '/tmp:rw,noexec,nosuid,size=16m',
                '--cap-drop=ALL',
                '--security-opt=no-new-privileges',
                '--memory='.$memoryLimit,
                '--memory-swap='.$memorySwap,
                '--cpus='.$cpus,
                '--pids-limit='.$pidsLimit,
                '--user',
                $user,
                '-v',
                $workspaceDir.':/workspace:ro',
                '-w',
                '/workspace',
                $image,
                'php',
                '-d',
                'open_basedir=/workspace:/tmp',
                '-d',
                'display_errors=1',
                '/workspace/run_tests.php',
            ];

            try {
                $process = Process::timeout($timeoutSeconds)->run($command);
            } catch (ProcessTimedOutException) {
                return new ChallengeExecutionResult(
                    status: 'timeout',
                    cases: [],
                    executionMs: $this->elapsedMs($startedAt),
                    errorMessage: "Execution exceeded the {$timeoutSeconds}s wall-clock time limit.",
                );
            }

            $elapsedMs = $this->elapsedMs($startedAt);
            $stdout = $process->output();
            $stderr = $process->errorOutput();
            $exitCode = $process->exitCode() ?? 1;

            if (
                $exitCode === 124
                || $exitCode === 137
                || str_contains($stdout.$stderr, 'Maximum execution time')
            ) {
                return new ChallengeExecutionResult(
                    status: 'timeout',
                    cases: [],
                    executionMs: $elapsedMs,
                    stdout: $stdout,
                    stderr: $stderr,
                    errorMessage: "Execution exceeded the {$timeoutSeconds}s time limit or {$memoryLimit} memory cap.",
                );
            }

            $cases = $this->parseStructuredResults($stdout, $request->scope);

            if ($cases === null) {
                $diagnostic = $this->extractDiagnosticError($stdout, $stderr);

                return new ChallengeExecutionResult(
                    status: 'error',
                    cases: [],
                    executionMs: $elapsedMs,
                    stdout: $stdout,
                    stderr: $stderr,
                    errorMessage: $diagnostic,
                );
            }

            $allPassed = $exitCode === 0
                && count($cases) > 0
                && count(array_filter($cases, static fn (array $c): bool => ! $c['passed'])) === 0;

            return new ChallengeExecutionResult(
                status: $allPassed ? 'passed' : 'failed',
                cases: $cases,
                executionMs: $elapsedMs,
                stdout: $stdout,
                stderr: $stderr,
            );
        } finally {
            File::deleteDirectory($workspaceDir);
        }
    }

    /**
     * @return list<array{name: string, passed: bool, expected: string|null, actual: string|null, error: string|null, scope: string}>|null
     */
    private function parseStructuredResults(string $stdout, string $scope): ?array
    {
        $lines = array_reverse(array_filter(explode("\n", trim($stdout))));

        foreach ($lines as $line) {
            try {
                /** @var mixed $decoded */
                $decoded = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                continue;
            }

            if (! is_array($decoded) || ! is_array($decoded['results'] ?? null)) {
                continue;
            }

            $cases = [];
            foreach ($decoded['results'] as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $cases[] = [
                    'name' => (string) ($entry['name'] ?? 'Test case'),
                    'passed' => (bool) ($entry['passed'] ?? false),
                    'expected' => isset($entry['expected']) ? (string) $entry['expected'] : null,
                    'actual' => isset($entry['actual']) ? (string) $entry['actual'] : null,
                    'error' => isset($entry['error']) ? (string) $entry['error'] : null,
                    'scope' => $scope,
                ];
            }

            return $cases;
        }

        return null;
    }

    private function extractDiagnosticError(string $stdout, string $stderr): string
    {
        $trimmedStderr = trim($stderr);
        if ($trimmedStderr !== '') {
            return $trimmedStderr;
        }

        $trimmedStdout = trim($stdout);
        if ($trimmedStdout !== '') {
            $firstLine = trim(explode("\n", $trimmedStdout)[0]);
            if ($firstLine !== '') {
                return $firstLine;
            }
        }

        return 'Docker PHP sandbox did not return structured test output.';
    }

    private function elapsedMs(int $startedAtNs): int
    {
        return (int) round((hrtime(true) - $startedAtNs) / 1_000_000);
    }
}
