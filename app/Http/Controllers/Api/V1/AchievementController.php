<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AchievementDefinitionResource;
use App\Http\Resources\Api\V1\UserAchievementResource;
use App\Models\AchievementDefinition;
use App\Models\Game;
use App\Models\GameSave;
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
            'slugs' => 'required|array|min:1|max:10',
            'slugs.*' => 'required|string|max:100',
        ]);

        // Plausibility check: cross-reference with cloud save data
        $save = GameSave::where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->where('save_key', 'main')
            ->first();

        $progressResults = [];
        if ($save && isset($save->data['progress']['results'])) {
            $progressResults = $save->data['progress']['results'];
        }

        // Rate limit: max 10 new achievements per request, max existing already checked by validation
        $alreadyUnlocked = UserAchievement::where('user_id', $request->user()->id)
            ->whereHas('achievementDefinition', fn ($q) => $q->where('game_id', $game->id))
            ->count();

        $definitions = AchievementDefinition::where('game_id', $game->id)
            ->whereIn('slug', $validated['slugs'])
            ->where('is_active', true)
            ->get();

        $unlocked = [];
        foreach ($definitions as $definition) {
            // Plausibility: if game has save data, check basic conditions
            if (! empty($progressResults)) {
                $totalCompleted = count($progressResults);
                $skip = $this->checkAchievementPlausibility($definition->slug, $totalCompleted, $progressResults);
                if ($skip) {
                    continue;
                }
            }

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

    /**
     * Basic plausibility check: reject achievements that clearly don't match progress.
     * Returns true if the achievement should be SKIPPED (implausible).
     */
    private function checkAchievementPlausibility(string $slug, int $totalCompleted, array $results): bool
    {
        // Map achievement slugs to minimum puzzle count required
        // This is a loose sanity check — exact conditions live client-side
        $minPuzzles = [
            'first_puzzle' => 1,
            'ten_puzzles' => 10,
            'fifty_puzzles' => 50,
            'hundred_puzzles' => 100,
            'two_fifty_puzzles' => 250,
            'five_hundred_puzzles' => 500,
            'thousand_puzzles' => 1000,
        ];

        if (isset($minPuzzles[$slug]) && $totalCompleted < $minPuzzles[$slug]) {
            return true; // Skip — not enough puzzles completed
        }

        return false;
    }
}
