<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'product_id',
        'reference_name',
        'product_type',
        'grant_type',
        'grant_id',
        'price',
        'currency',
        'display_name',
        'description',
        'review_notes',
        'review_screenshot_path',
        'sort_order',
        'is_active',
        'apple_state',
        'apple_sync_error',
        'apple_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'apple_synced_at' => 'datetime',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
