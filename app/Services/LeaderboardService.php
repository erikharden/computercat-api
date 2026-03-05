<?php

namespace App\Services;

use App\Models\LeaderboardEntry;
use App\Models\LeaderboardType;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LeaderboardService
{
    public function currentPeriodKey(LeaderboardType $type): string
    {
        return match ($type->period) {
            'daily' => Carbon::now()->format('Y-m-d'),
            'weekly' => Carbon::now()->format('o-\\WW'),
            'monthly' => Carbon::now()->format('Y-m'),
            'all_time' => 'all',
        };
    }

    public function getEntries(LeaderboardType $type, string $periodKey): Collection
    {
        $direction = $type->sort_direction === 'asc' ? 'asc' : 'desc';

        $entries = LeaderboardEntry::where('leaderboard_type_id', $type->id)
            ->where('period_key', $periodKey)
            ->with('user')
            ->orderBy('score', $direction)
            ->limit($type->max_entries_per_period)
            ->get();

        // Add rank
        $entries->each(function ($entry, $index) {
            $entry->rank = $index + 1;
        });

        return $entries;
    }

    public function submitScore(LeaderboardType $type, User $user, int $score, ?array $metadata): LeaderboardEntry
    {
        $periodKey = $this->currentPeriodKey($type);

        $existing = LeaderboardEntry::where('leaderboard_type_id', $type->id)
            ->where('user_id', $user->id)
            ->where('period_key', $periodKey)
            ->first();

        if ($existing) {
            $isBetter = $type->sort_direction === 'asc'
                ? $score < $existing->score
                : $score > $existing->score;

            if ($isBetter) {
                $existing->update([
                    'score' => $score,
                    'metadata' => $metadata,
                    'submitted_at' => now(),
                ]);
            }

            return $existing->fresh();
        }

        return LeaderboardEntry::create([
            'leaderboard_type_id' => $type->id,
            'user_id' => $user->id,
            'period_key' => $periodKey,
            'score' => $score,
            'metadata' => $metadata,
            'submitted_at' => now(),
        ]);
    }

    public function getUserRank(LeaderboardType $type, User $user, string $periodKey): ?array
    {
        $entry = LeaderboardEntry::where('leaderboard_type_id', $type->id)
            ->where('user_id', $user->id)
            ->where('period_key', $periodKey)
            ->with('user')
            ->first();

        if (! $entry) {
            return null;
        }

        $comparator = $type->sort_direction === 'asc' ? '<' : '>';

        $rank = LeaderboardEntry::where('leaderboard_type_id', $type->id)
            ->where('period_key', $periodKey)
            ->where('score', $comparator, $entry->score)
            ->count() + 1;

        $entry->rank = $rank;

        return [
            'entry' => $entry,
            'rank' => $rank,
        ];
    }
}
