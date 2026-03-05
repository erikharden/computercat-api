<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AchievementDefinitionResource;
use App\Http\Resources\Api\V1\UserAchievementResource;
use App\Models\AchievementDefinition;
use App\Models\Game;
use App\Models\UserAchievement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function index(Game $game): JsonResponse
    {
        $definitions = AchievementDefinition::where('game_id', $game->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => AchievementDefinitionResource::collection($definitions),
        ]);
    }

    public function me(Request $request, Game $game): JsonResponse
    {
        $achievements = UserAchievement::where('user_id', $request->user()->id)
            ->whereHas('achievementDefinition', fn ($q) => $q->where('game_id', $game->id))
            ->with('achievementDefinition')
            ->orderBy('unlocked_at')
            ->get();

        return response()->json([
            'data' => UserAchievementResource::collection($achievements),
        ]);
    }

    public function store(Request $request, Game $game): JsonResponse
    {
        $validated = $request->validate([
            'slugs' => 'required|array|min:1',
            'slugs.*' => 'required|string',
        ]);

        $definitions = AchievementDefinition::where('game_id', $game->id)
            ->whereIn('slug', $validated['slugs'])
            ->where('is_active', true)
            ->get();

        $unlocked = [];
        foreach ($definitions as $definition) {
            $achievement = UserAchievement::firstOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'achievement_definition_id' => $definition->id,
                ],
                [
                    'unlocked_at' => now(),
                    'created_at' => now(),
                ]
            );

            if ($achievement->wasRecentlyCreated) {
                $achievement->load('achievementDefinition');
                $unlocked[] = new UserAchievementResource($achievement);
            }
        }

        return response()->json([
            'unlocked' => $unlocked,
            'already_unlocked' => count($definitions) - count($unlocked),
        ], 201);
    }
}
