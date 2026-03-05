<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardEntry extends Model
{
    protected $fillable = [
        'leaderboard_type_id',
        'user_id',
        'period_key',
        'score',
        'metadata',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'metadata' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function leaderboardType(): BelongsTo
    {
        return $this->belongsTo(LeaderboardType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
