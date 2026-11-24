<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $challenge_id
 * @property string $mode
 * @property string $language
 * @property array<string, string> $files_json
 * @property string|null $decision_choice
 * @property string $status
 * @property int $passed_tests
 * @property int $total_tests
 * @property int $execution_ms
 * @property string|null $stdout
 * @property string|null $stderr
 * @property list<array{name: string, passed: bool, expected: mixed, actual: mixed, error: string|null, scope?: string}>|null $results_json
 * @property int $xp_awarded
 */
#[Fillable([
    'user_id',
    'challenge_id',
    'mode',
    'language',
    'files_json',
    'decision_choice',
    'status',
    'passed_tests',
    'total_tests',
    'execution_ms',
    'stdout',
    'stderr',
    'results_json',
    'xp_awarded',
])]
class ChallengeSubmission extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'files_json' => 'array',
            'results_json' => 'array',
            'passed_tests' => 'integer',
            'total_tests' => 'integer',
            'execution_ms' => 'integer',
            'xp_awarded' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Challenge, $this>
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }
}
