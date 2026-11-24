<?php

namespace App\Data;

readonly class ChallengeExecutionResult
{
    /**
     * @param  'passed'|'failed'|'timeout'|'error'  $status
     * @param  list<array{name: string, passed: bool, expected: string|null, actual: string|null, error: string|null, scope: string}>  $cases
     */
    public function __construct(
        public string $status,
        public array $cases,
        public int $executionMs = 0,
        public string $stdout = '',
        public string $stderr = '',
        public ?string $errorMessage = null,
    ) {}

    public function passedCount(): int
    {
        return count(array_filter($this->cases, static fn (array $case): bool => $case['passed']));
    }

    public function totalCount(): int
    {
        return count($this->cases);
    }

    public function allPassed(): bool
    {
        return $this->status === 'passed' && $this->totalCount() > 0 && $this->passedCount() === $this->totalCount();
    }
}
