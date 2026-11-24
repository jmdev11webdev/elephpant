<?php

namespace App\Services;

use App\Contracts\ChallengeRunnerInterface;
use App\Data\ChallengeExecutionRequest;
use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use App\Models\User;

class ChallengeGradingService
{
    public function __construct(
        private readonly ChallengeRunnerInterface $runner,
        private readonly ProgressionService $progression,
    ) {}

    /**
     * Run player code against public tests only (no XP awarded).
     *
     * @param  array<string, string>  $files
     * @return array<string, mixed>
     */
    public function runPublic(
        User $user,
        Challenge $challenge,
        array $files,
        string $language = 'php',
    ): array {
        $result = $this->runner->execute(new ChallengeExecutionRequest(
            challengeSlug: $challenge->slug,
            language: $language,
            files: $files,
            testHarness: $challenge->public_tests,
            scope: 'public',
        ));

        ChallengeSubmission::query()->create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'mode' => 'public',
            'language' => $language,
            'files_json' => $files,
            'status' => $result->status,
            'passed_tests' => $result->passedCount(),
            'total_tests' => $result->totalCount(),
            'execution_ms' => $result->executionMs,
            'stdout' => $result->stdout,
            'stderr' => $result->stderr,
            'results_json' => $result->cases,
            'xp_awarded' => 0,
        ]);

        return [
            'mode' => 'public',
            'status' => $result->status,
            'passedTests' => $result->passedCount(),
            'totalTests' => $result->totalCount(),
            'executionMs' => $result->executionMs,
            'cases' => $result->cases,
            'stderr' => $result->stderr,
            'errorMessage' => $result->errorMessage,
        ];
    }

    /**
     * Grade player code against both public and server-only hidden tests
     * (plus technical decision validation where applicable) and update progression.
     *
     * @param  array<string, string>  $files
     * @return array<string, mixed>
     */
    public function submit(
        User $user,
        Challenge $challenge,
        array $files,
        string $language = 'php',
        ?string $decisionChoice = null,
    ): array {
        $publicResult = $this->runner->execute(new ChallengeExecutionRequest(
            challengeSlug: $challenge->slug,
            language: $language,
            files: $files,
            testHarness: $challenge->public_tests,
            scope: 'public',
        ));

        $hiddenResult = $this->runner->execute(new ChallengeExecutionRequest(
            challengeSlug: $challenge->slug,
            language: $language,
            files: $files,
            testHarness: $challenge->hidden_tests,
            scope: 'hidden',
        ));

        // Mask hidden test internals so hidden test code / exact payloads never leak
        $sanitizedHiddenCases = array_map(
            static fn (array $case, int $index): array => [
                'name' => 'Hidden Test #'.($index + 1),
                'passed' => $case['passed'],
                'expected' => null,
                'actual' => null,
                'error' => $case['passed'] ? null : ($case['error'] ?? 'Hidden assertion did not pass.'),
                'scope' => 'hidden',
            ],
            $hiddenResult->cases,
            array_keys($hiddenResult->cases),
        );

        $combinedCases = [...$publicResult->cases, ...$sanitizedHiddenCases];

        $decisionPassed = true;
        if ($challenge->correct_decision_id !== null && $challenge->correct_decision_id !== '') {
            $decisionPassed = $decisionChoice === $challenge->correct_decision_id;
            $combinedCases[] = [
                'name' => 'Architectural Technical Decision',
                'passed' => $decisionPassed,
                'expected' => null,
                'actual' => null,
                'error' => $decisionPassed ? null : 'Selected architectural decision does not safely remediate the issue.',
                'scope' => 'decision',
            ];
        }

        $status = match (true) {
            $publicResult->status === 'timeout' || $hiddenResult->status === 'timeout' => 'timeout',
            $publicResult->status === 'error' || $hiddenResult->status === 'error' => 'error',
            $publicResult->allPassed() && $hiddenResult->allPassed() && $decisionPassed => 'passed',
            default => 'failed',
        };

        $passedCount = count(array_filter($combinedCases, static fn (array $c): bool => $c['passed']));
        $totalCount = count($combinedCases);
        $executionMs = $publicResult->executionMs + $hiddenResult->executionMs;

        $rewards = [
            'xpAwarded' => 0,
            'firstCompletion' => false,
            'disciplineRank' => null,
            'unlockedBadges' => [],
            'unlockedTitles' => [],
            'unlockedTriggerId' => null,
        ];

        if ($status === 'passed') {
            $rewards = $this->progression->recordPassedSubmission($user, $challenge);
        } else {
            $this->progression->resetDisciplineStreak($user, $challenge->discipline);
        }

        ChallengeSubmission::query()->create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'mode' => 'submit',
            'language' => $language,
            'files_json' => $files,
            'decision_choice' => $decisionChoice,
            'status' => $status,
            'passed_tests' => $passedCount,
            'total_tests' => $totalCount,
            'execution_ms' => $executionMs,
            'stdout' => $publicResult->stdout,
            'stderr' => trim($publicResult->stderr."\n".$hiddenResult->stderr),
            'results_json' => $combinedCases,
            'xp_awarded' => $rewards['xpAwarded'],
        ]);

        return [
            'mode' => 'submit',
            'status' => $status,
            'passedTests' => $passedCount,
            'totalTests' => $totalCount,
            'executionMs' => $executionMs,
            'cases' => $combinedCases,
            'stderr' => $publicResult->stderr !== '' ? $publicResult->stderr : $hiddenResult->stderr,
            'errorMessage' => $publicResult->errorMessage ?? $hiddenResult->errorMessage,
            'rewards' => $rewards,
        ];
    }
}
