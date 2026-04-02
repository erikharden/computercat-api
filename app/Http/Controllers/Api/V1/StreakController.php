<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\PlayerStreak;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StreakController extends Controller
{
    public function show(Request $request, Game $game, string $key): JsonResponse
    {
        $streak = PlayerStreak::where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->where('streak_key', $key)
            ->first();

        if (! $streak) {
            return response()->json([
                'data' => [
                    'streak_key' => $key,
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'last_activity_date' => null,
                    'freeze_balance' => 0,
                ],
            ]);
        }

        return response()->json([
            'data' => [
                'streak_key' => $streak->streak_key,
                'current_streak' => $streak->current_streak,
                'longest_streak' => $streak->longest_streak,
                'last_activity_date' => $streak->last_activity_date?->toDateString(),
                'freeze_balance' => $streak->freeze_balance,
            ],
        ]);
    }

    public function record(Request $request, Game $game, string $key): JsonResponse
    {
        $user = $request->user();
        $today = now()->toDateString();

        $streak = PlayerStreak::firstOrCreate(
            [
                'user_id' => $user->id,
                'game_id' => $game->id,
                'streak_key' => $key,
            ],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'freeze_balance' => 0,
            ]
        );

        $lastDate = $streak->last_activity_date?->toDateString();

        // Already recorded today — no-op
        if ($lastDate === $today) {
            return $this->streakResponse($streak);
        }

        $yesterday = Carbon::yesterday()->toDateString();
        $dayBeforeYesterday = Carbon::yesterday()->subDay()->toDateString();
        $settings = $game->settings ?? [];
        $maxFreezes = $settings['streaks'][$key]['max_freezes'] ?? 0;
        $freezeEarnInterval = $settings['streaks'][$key]['freeze_earn_interval'] ?? 0;

        if ($lastDate === $yesterday) {
            // Consecutive day — increment streak
            $streak->current_streak++;
        } elseif ($lastDate === $dayBeforeYesterday && $streak->freeze_balance > 0) {
            // Missed one day but has freeze — use it
            $streak->freeze_balance--;
            $streak->last_freeze_date = Carbon::parse($yesterday);
            $streak->current_streak++;
        } elseif ($lastDate === null) {
            // First activity ever
            $streak->current_streak = 1;
        } else {
            // Gap too large — reset
            $streak->current_streak = 1;
        }

        // Update longest streak
        if ($streak->current_streak > $streak->longest_streak) {
            $streak->longest_streak = $streak->current_streak;
        }

        // Earn freezes based on interval
        if ($freezeEarnInterval > 0 && $streak->current_streak > 0 && $streak->current_streak % $freezeEarnInterval === 0) {
            if ($streak->freeze_balance < $maxFreezes) {
                $streak->freeze_balance++;
            }
        }

        $streak->last_activity_date = $today;
        $streak->save();

        return $this->streakResponse($streak);
    }

    private function streakResponse(PlayerStreak $streak): JsonResponse
    {
        return response()->json([
            'data' => [
                'streak_key' => $streak->streak_key,
                'current_streak' => $streak->current_streak,
                'longest_streak' => $streak->longest_streak,
                'last_activity_date' => $streak->last_activity_date?->toDateString(),
                'freeze_balance' => $streak->freeze_balance,
            ],
        ]);
    }
}
