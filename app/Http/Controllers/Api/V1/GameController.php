<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GameResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function index(): JsonResponse
    {
        $games = Game::where('is_active', true)->get();

        return response()->json([
            'data' => GameResource::collection($games),
        ]);
    }

    public function show(Game $game): JsonResponse
    {
        $game->load(['leaderboardTypes' => fn ($q) => $q->where('is_active', true)]);

        return response()->json([
            'data' => new GameResource($game),
        ]);
    }
}
