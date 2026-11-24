<?php

namespace App\Data;

readonly class ChallengeExecutionRequest
{
    /**
     * @param  array<string, string>  $files
     */
    public function __construct(
        public string $challengeSlug,
        public string $language,
        public array $files,
        public string $testHarness,
        public string $scope = 'public',
    ) {}
}
