<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PruneAnonymousUsers extends Command
{
    protected $signature = 'users:prune-anonymous {--days=90 : Days of inactivity before pruning}';
    protected $description = 'Delete anonymous users with no activity for N days and no meaningful data';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        // Find anonymous users who haven't been seen since cutoff
        // (or were never seen and were created before cutoff)
        $candidates = User::where('is_anonymous', true)
            ->where(function ($q) use ($cutoff) {
                $q->where('last_seen_at', '<', $cutoff)
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNull('last_seen_at')
                            ->where('created_at', '<', $cutoff);
                    });
            })
            ->get();

        $pruned = 0;
        $skipped = 0;

        foreach ($candidates as $user) {
            // Skip users who have purchases (even anonymous)
            if ($user->purchases()->exists()) {
                $skipped++;

                continue;
            }

            // Skip users who have leaderboard entries (they contributed content)
            if ($user->leaderboardEntries()->exists()) {
                // Delete their entries too since the user is stale
                $user->leaderboardEntries()->delete();
            }

            // Cascade delete: tokens, saves, achievements, then user
            $user->tokens()->delete();
            $user->gameSaves()->delete();
            $user->achievements()->delete();
            $user->delete();
            $pruned++;
        }

        $this->info("Pruned {$pruned} anonymous users inactive for {$days}+ days. Skipped {$skipped} (have purchases).");

        return self::SUCCESS;
    }
}
