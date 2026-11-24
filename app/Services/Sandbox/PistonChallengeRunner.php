<?php

namespace App\Services\Sandbox;

use App\Contracts\ChallengeRunnerInterface;
use App\Data\ChallengeExecutionRequest;
use App\Data\ChallengeExecutionResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JsonException;

class PistonChallengeRunner implements ChallengeRunnerInterface
{
    public function execute(ChallengeExecutionRequest $request): ChallengeExecutionResult
    {
        $baseUrl = (string) config('sandbox.piston.base_url', 'http://127.0.0.1:2000');
        $apiKey = config('sandbox.piston.api_key');
        $httpTimeout = (int) config('sandbox.piston.http_timeout_seconds', 10);
        $runTimeoutMs = (int) config('sandbox.piston.run_timeout_ms', 3000);
        $compileTimeoutMs = (int) config('sandbox.piston.compile_timeout_ms', 5000);
        $memoryLimit = (int) config('sandbox.piston.memory_limit_bytes', 134217728);
        /** @var array<string, string> $runtimes */
        $runtimes = config('sandbox.piston.runtimes', ['php' => '8.2.3']);

        $language = $request->language === 'sql' ? 'php' : $request->language;
        $version = $runtimes[$language] ?? '*';

        $pistonFiles = [
            [
                'name' => 'run_tests.php',
                'content' => $request->testHarness,
            ],
        ];

        foreach ($request->files as $filename => $content) {
            $safeName = basename($filename);
            if ($safeName === '' || $safeName === 'run_tests.php') {
                continue;
            }

            $pistonFiles[] = [
                'name' => $safeName,
                'content' => $content,
            ];
        }

        $startedAt = hrtime(true);

        try {
            $client = Http::timeout($httpTimeout)->acceptJson();

            if (is_string($apiKey) && $apiKey !== '') {
                $client = $client->withHeaders(['Authorization' => $apiKey]);
            }

            $response = $client->post("{$baseUrl}/api/v2/execute", [
                'language' => $language,
                'version' => $version,
                'files' => $pistonFiles,
                'run_timeout' => $runTimeoutMs,
                'compile_timeout' => $compileTimeoutMs,
                'run_memory_limit' => $memoryLimit,
            ]);
        } catch (ConnectionException $exception) {
            return new ChallengeExecutionResult(
                status: 'error',
                cases: [],
                executionMs: $this->elapsedMs($startedAt),
                stderr: $exception->getMessage(),
                errorMessage: 'Isolated Piston sandbox is unreachable. Start docker-compose.sandbox.yml or check SANDBOX_PISTON_URL.',
            );
        }

        $elapsedMs = $this->elapsedMs($startedAt);

        if (! $response->successful()) {
            return new ChallengeExecutionResult(
                status: 'error',
                cases: [],
                executionMs: $elapsedMs,
                stderr: $response->body(),
                errorMessage: "Sandbox returned HTTP {$response->status()}.",
            );
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json() ?? [];
        /** @var array<string, mixed> $run */
        $run = is_array($payload['run'] ?? null) ? $payload['run'] : [];

        $stdout = (string) ($run['stdout'] ?? '');
        $stderr = (string) ($run['stderr'] ?? '');
        $signal = $run['signal'] ?? null;
        $exitCode = (int) ($run['code'] ?? 0);

        if ($signal === 'SIGKILL' || $exitCode === 137) {
            return new ChallengeExecutionResult(
                status: 'timeout',
                cases: [],
                executionMs: $elapsedMs,
                stdout: $stdout,
                stderr: $stderr,
                errorMessage: "Execution exceeded the {$runTimeoutMs}ms time limit or memory cap.",
            );
        }

        $cases = $this->parseStructuredResults($stdout, $request->scope);

        if ($cases === null) {
            return new ChallengeExecutionResult(
                status: 'error',
                cases: [],
                executionMs: $elapsedMs,
                stdout: $stdout,
                stderr: $stderr,
                errorMessage: $stderr !== '' ? trim($stderr) : 'Execution did not produce structured test output.',
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

    private function elapsedMs(int $startedAtNs): int
    {
        return (int) round((hrtime(true) - $startedAtNs) / 1_000_000);
    }
}
