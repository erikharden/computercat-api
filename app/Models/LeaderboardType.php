<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaderboardType extends Model
{
    protected $fillable = [
        'game_id',
        'slug',
        'name',
        'sort_direction',
        'score_label',
        'period',
        'max_entries_per_period',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'max_entries_per_period' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LeaderboardEntry::class);
    }
}
