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
                $skip = $this->checkAchievementPlausibility($definition->slug, $totalCompleted, $progressResults, $game);
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
     * Generic plausibility check: reject achievements that clearly don't match progress.
     * Returns true if the achievement should be SKIPPED (implausible).
     *
     * Uses a simple heuristic: if the slug contains a number pattern (e.g., "fifty_puzzles",
     * "100_wins"), extract it and compare against total completed items in the save.
     * Games can also define explicit thresholds in settings.achievement_thresholds.
     */
    private function checkAchievementPlausibility(string $slug, int $totalCompleted, array $results, Game $game): bool
    {
        // Check game-configured thresholds first (settings.achievement_thresholds)
        $thresholds = $game->settings['achievement_thresholds'] ?? [];
        if (isset($thresholds[$slug])) {
            return $totalCompleted < (int) $thresholds[$slug];
        }

        // Heuristic: extract number from slug (e.g., "complete_100" → 100)
        if (preg_match('/(\d+)/', $slug, $m)) {
            $threshold = (int) $m[1];
            // Only apply if the number seems like a count threshold (1-10000)
            if ($threshold > 0 && $threshold <= 10000 && $totalCompleted < $threshold) {
                return true;
            }
        }

        return false;
    }
}
