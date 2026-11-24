<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $slot
 * @property string|null $label
 * @property string $current_zone
 * @property int $player_x
 * @property int $player_y
 * @property string $facing
 * @property int $playtime_seconds
 * @property CarbonImmutable|null $saved_at
 */
#[Fillable([
    'user_id',
    'slot',
    'label',
    'current_zone',
    'player_x',
    'player_y',
    'facing',
    'playtime_seconds',
    'saved_at',
])]
class GameSave extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot' => 'integer',
            'player_x' => 'integer',
            'player_y' => 'integer',
            'playtime_seconds' => 'integer',
            'saved_at' => 'immutable_datetime',
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
