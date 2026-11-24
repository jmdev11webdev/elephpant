<?php

namespace App\Contracts;

use App\Data\ChallengeExecutionRequest;
use App\Data\ChallengeExecutionResult;

interface ChallengeRunnerInterface
{
    /**
     * Execute untrusted player code against a test harness inside an isolated sandbox.
     */
    public function execute(ChallengeExecutionRequest $request): ChallengeExecutionResult;
}
