<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerStreak extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'streak_key',
        'current_streak',
        'longest_streak',
        'last_activity_date',
        'freeze_balance',
        'last_freeze_date',
    ];

    protected function casts(): array
    {
        return [
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_activity_date' => 'date',
            'freeze_balance' => 'integer',
            'last_freeze_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
