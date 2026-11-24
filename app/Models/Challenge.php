<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $discipline
 * @property string|null $district
 * @property string $category
 * @property string $difficulty
 * @property int|null $sort_order
 * @property string $evaluation_type
 * @property list<string> $supported_languages
 * @property int $xp_reward
 * @property int $estimated_minutes
 * @property string $skill_key
 * @property string|null $map_trigger_id
 * @property string|null $terminal_key
 * @property string $prompt_markdown
 * @property array<string, string> $starter_files
 * @property list<string> $hints
 * @property list<array{id: string, label: string}>|null $decision_options
 * @property string|null $correct_decision_id
 * @property string $public_tests
 * @property string $hidden_tests
 */
#[Fillable([
    'slug',
    'title',
    'discipline',
    'district',
    'category',
    'difficulty',
    'sort_order',
    'evaluation_type',
    'supported_languages',
    'xp_reward',
    'estimated_minutes',
    'skill_key',
    'map_trigger_id',
    'terminal_key',
    'prompt_markdown',
    'starter_files',
    'hints',
    'decision_options',
    'correct_decision_id',
    'public_tests',
    'hidden_tests',
])]
#[Hidden(['hidden_tests', 'correct_decision_id'])]
class Challenge extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'supported_languages' => 'array',
            'starter_files' => 'array',
            'hints' => 'array',
            'decision_options' => 'array',
            'xp_reward' => 'integer',
            'estimated_minutes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<ChallengeSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(ChallengeSubmission::class);
    }
}
