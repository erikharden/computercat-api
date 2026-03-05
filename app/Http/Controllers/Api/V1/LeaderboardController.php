<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LeaderboardEntryResource;
use App\Models\Game;
use App\Models\LeaderboardType;
use App\Services\AntiCheatService;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function __construct(
        private LeaderboardService $leaderboardService,
        private AntiCheatService $antiCheatService,
    ) {}

    public function index(Game $game, string $type): JsonResponse
    {
        $leaderboardType = $this->resolveType($game, $type);
        if (! $leaderboardType) {
            return response()->json(['message' => 'Leaderboard type not found.'], 404);
        }

        $periodKey = $this->leaderboardService->currentPeriodKey($leaderboardType);
        $entries = $this->leaderboardService->getEntries($leaderboardType, $periodKey);

        return response()->json([
            'data' => LeaderboardEntryResource::collection($entries),
            'period_key' => $periodKey,
        ]);
    }

    public function show(Game $game, string $type, string $periodKey): JsonResponse
    {
        $leaderboardType = $this->resolveType($game, $type);
        if (! $leaderboardType) {
            return response()->json(['message' => 'Leaderboard type not found.'], 404);
        }

        $entries = $this->leaderboardService->getEntries($leaderboardType, $periodKey);

        return response()->json([
            'data' => LeaderboardEntryResource::collection($entries),
            'period_key' => $periodKey,
        ]);
    }

    public function store(Request $request, Game $game, string $type): JsonResponse
    {
        $leaderboardType = $this->resolveType($game, $type);
        if (! $leaderboardType) {
            return response()->json(['message' => 'Leaderboard type not found.'], 404);
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:0',
            'metadata' => 'nullable|array',
        ]);

        $check = $this->antiCheatService->check($leaderboardType, $validated['score'], $validated['metadata'] ?? null);
        if (! $check['passed']) {
            return response()->json(['message' => $check['reason']], 422);
        }

        $entry = $this->leaderboardService->submitScore(
            $leaderboardType,
            $request->user(),
            $validated['score'],
            $validated['metadata'] ?? null,
        );

        return response()->json([
            'data' => new LeaderboardEntryResource($entry->load('user')),
        ], 201);
    }

    public function me(Request $request, Game $game, string $type): JsonResponse
    {
        $leaderboardType = $this->resolveType($game, $type);
        if (! $leaderboardType) {
            return response()->json(['message' => 'Leaderboard type not found.'], 404);
        }

        $periodKey = $this->leaderboardService->currentPeriodKey($leaderboardType);
        $result = $this->leaderboardService->getUserRank($leaderboardType, $request->user(), $periodKey);

        if (! $result) {
            return response()->json(['data' => null, 'period_key' => $periodKey]);
        }

        return response()->json([
            'data' => new LeaderboardEntryResource($result['entry']),
            'rank' => $result['rank'],
            'period_key' => $periodKey,
        ]);
    }

    private function resolveType(Game $game, string $slug): ?LeaderboardType
    {
        return LeaderboardType::where('game_id', $game->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}
