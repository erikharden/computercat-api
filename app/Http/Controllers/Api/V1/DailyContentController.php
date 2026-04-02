<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyContentPool;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class DailyContentController extends Controller
{
    public function show(Game $game, string $poolKey): JsonResponse
    {
        return $this->contentForDate($game, $poolKey, now()->toDateString());
    }

    public function showDate(Game $game, string $poolKey, string $date): JsonResponse
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['message' => 'Invalid date format. Use YYYY-MM-DD.'], 422);
        }

        if ($date > now()->toDateString()) {
            return response()->json(['message' => 'Cannot request future dates.'], 403);
        }

        return $this->contentForDate($game, $poolKey, $date);
    }

    private function contentForDate(Game $game, string $poolKey, string $date): JsonResponse
    {
        $pool = DailyContentPool::where('game_id', $game->id)
            ->where('pool_key', $poolKey)
            ->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $date);
            })
            ->first();

        if (! $pool) {
            return response()->json(['message' => 'Content pool not found.'], 404);
        }

        $content = $pool->contentForDate($date);

        return response()->json([
            'data' => $content,
            'date' => $date,
            'pool_key' => $poolKey,
        ]);
    }
}
