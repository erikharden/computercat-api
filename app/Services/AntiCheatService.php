<?php

namespace App\Services;

use App\Models\LeaderboardType;

class AntiCheatService
{
    public function check(LeaderboardType $type, int $score, ?array $metadata): array
    {
        // Basic plausibility checks
        if ($score < 0) {
            return ['passed' => false, 'reason' => 'Score cannot be negative.'];
        }

        // For time-based leaderboards (asc sort = lower is better = time in seconds)
        if ($type->sort_direction === 'asc') {
            // Minimum 5 seconds for any puzzle
            if ($score < 5) {
                return ['passed' => false, 'reason' => 'Score is implausibly low.'];
            }
        }

        // Maximum score sanity check (24 hours in seconds)
        if ($type->sort_direction === 'asc' && $score > 86400) {
            return ['passed' => false, 'reason' => 'Score exceeds maximum allowed value.'];
        }

        return ['passed' => true, 'reason' => null];
    }
}
