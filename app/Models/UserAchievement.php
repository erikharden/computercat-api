<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'achievement_definition_id',
        'unlocked_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievementDefinition(): BelongsTo
    {
        return $this->belongsTo(AchievementDefinition::class);
    }
}
