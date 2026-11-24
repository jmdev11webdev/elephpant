<?php

namespace App\Services\Sandbox;

use App\Contracts\ChallengeRunnerInterface;
use App\Data\ChallengeExecutionRequest;
use App\Data\ChallengeExecutionResult;

class FakeChallengeRunner implements ChallengeRunnerInterface
{
    /** @var 'passed'|'failed'|'timeout'|'error' */
    private static string $forcedStatus = 'passed';

    private static ?string $forcedError = null;

    public static function forcePass(): void
    {
        self::$forcedStatus = 'passed';
        self::$forcedError = null;
    }

    public static function forceFail(): void
    {
        self::$forcedStatus = 'failed';
        self::$forcedError = null;
    }

    public static function forceTimeout(): void
    {
        self::$forcedStatus = 'timeout';
        self::$forcedError = 'Execution exceeded the 3000ms time limit.';
    }

    public static function reset(): void
    {
        self::forcePass();
    }

    public function execute(ChallengeExecutionRequest $request): ChallengeExecutionResult
    {
        if (self::$forcedStatus === 'timeout') {
            return new ChallengeExecutionResult(
                status: 'timeout',
                cases: [],
                executionMs: 3001,
                stderr: 'SIGKILL',
                errorMessage: self::$forcedError ?? 'Execution timed out.',
            );
        }

        if (self::$forcedStatus === 'failed') {
            return new ChallengeExecutionResult(
                status: 'failed',
                cases: [
                    [
                        'name' => "{$request->scope} verification check",
                        'passed' => false,
                        'expected' => 'expected_output',
                        'actual' => 'wrong_output',
                        'error' => null,
                        'scope' => $request->scope,
                    ],
                ],
                executionMs: 12,
            );
        }

        return new ChallengeExecutionResult(
            status: 'passed',
            cases: [
                [
                    'name' => "{$request->scope} verification check",
                    'passed' => true,
                    'expected' => 'ok',
                    'actual' => 'ok',
                    'error' => null,
                    'scope' => $request->scope,
                ],
            ],
            executionMs: 10,
        );
    }
}
