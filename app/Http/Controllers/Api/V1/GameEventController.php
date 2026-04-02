<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameEvent;
use Illuminate\Http\JsonResponse;

class GameEventController extends Controller
{
    public function index(Game $game): JsonResponse
    {
        $events = GameEvent::where('game_id', $game->id)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->get();

        return response()->json([
            'data' => $events->map(fn (GameEvent $event) => $this->formatEvent($event)),
        ]);
    }

    public function show(Game $game, string $slug): JsonResponse
    {
        $event = GameEvent::where('game_id', $game->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        return response()->json([
            'data' => $this->formatEvent($event),
        ]);
    }

    private function formatEvent(GameEvent $event): array
    {
        return [
            'slug' => $event->slug,
            'name' => $event->name,
            'description' => $event->description,
            'event_type' => $event->event_type,
            'starts_at' => $event->starts_at->toIso8601String(),
            'ends_at' => $event->ends_at->toIso8601String(),
            'settings' => $event->settings,
        ];
    }
}
