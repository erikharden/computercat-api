<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyContentPool extends Model
{
    protected $fillable = [
        'game_id',
        'pool_key',
        'content',
        'starts_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'starts_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the content item for a given date using deterministic selection.
     */
    public function contentForDate(string $date): mixed
    {
        $content = $this->content;

        if (empty($content)) {
            return null;
        }

        $hash = crc32($date . ':' . $this->id);
        $index = abs($hash) % count($content);

        return $content[$index];
    }
}
