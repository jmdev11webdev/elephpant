<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $discipline
 * @property int $xp
 * @property string $rank
 * @property int $completed_count
 * @property int $current_streak
 * @property int $best_streak
 */
#[Fillable([
    'user_id',
    'discipline',
    'xp',
    'rank',
    'completed_count',
    'current_streak',
    'best_streak',
])]
class UserDisciplineProgress extends Model
{
    protected $table = 'user_discipline_progress';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'xp' => 'integer',
            'completed_count' => 'integer',
            'current_streak' => 'integer',
            'best_streak' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
