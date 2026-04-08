<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\LeaderboardEntry;
use App\Models\PlayerStreak;
use App\Models\UserAchievement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerStatsController extends Controller
{
    public function me(Request $request, Game $game): JsonResponse
    {
        $user = $request->user();

        // Total games: count of leaderboard entries across all types/periods
        $leaderboardTypeIds = $game->leaderboardTypes()->pluck('id');

        $totalGames = LeaderboardEntry::where('user_id', $user->id)
            ->whereIn('leaderboard_type_id', $leaderboardTypeIds)
            ->count();

        // Best scores per leaderboard type
        $bestScores = [];
        $types = $game->leaderboardTypes()->get();
        foreach ($types as $type) {
            $entry = LeaderboardEntry::where('user_id', $user->id)
                ->where('leaderboard_type_id', $type->id)
                ->orderBy('score', $type->sort_direction === 'asc' ? 'asc' : 'desc')
                ->first();

            if ($entry) {
                $bestScores[$type->slug] = $entry->score;
            }
        }

        // Achievement count
        $achievementDefIds = $game->achievementDefinitions()->pluck('id');
        $achievementCount = UserAchievement::where('user_id', $user->id)
            ->whereIn('achievement_definition_id', $achievementDefIds)
            ->count();

        // Current streaks
        $streaks = PlayerStreak::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->streak_key => $s->current_streak]);

        return response()->json([
            'data' => [
                'total_games' => $totalGames,
                // Cast to objects so empty results serialize as {} not []
                'best_scores' => (object) $bestScores,
                'achievement_count' => $achievementCount,
                'current_streaks' => (object) $streaks->all(),
                'member_since' => $user->created_at->toIso8601String(),
                'last_active' => $user->last_seen_at?->toIso8601String(),
            ],
        ]);
    }
}
