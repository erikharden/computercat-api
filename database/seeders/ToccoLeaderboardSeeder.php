<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\LeaderboardType;
use Illuminate\Database\Seeder;

class ToccoLeaderboardSeeder extends Seeder
{
    public function run(): void
    {
        $game = Game::where('slug', 'tocco')->firstOrFail();

        LeaderboardType::updateOrCreate(
            ['game_id' => $game->id, 'slug' => 'daily-time'],
            [
                'name' => 'Daily Puzzle',
                'sort_direction' => 'asc',
                'score_label' => 'Time (seconds)',
                'period' => 'daily',
                'max_entries_per_period' => 100,
                'is_active' => true,
            ]
        );
    }
}
