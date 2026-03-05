<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSave extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'save_key',
        'data',
        'version',
        'checksum',
        'saved_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'version' => 'integer',
            'saved_at' => 'datetime',
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
