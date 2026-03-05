<?php

namespace Database\Seeders;

use App\Models\AchievementDefinition;
use App\Models\Game;
use Illuminate\Database\Seeder;

class ToccoAchievementSeeder extends Seeder
{
    public function run(): void
    {
        $game = Game::where('slug', 'tocco')->firstOrFail();

        $achievements = [
            // First steps
            ['slug' => 'first_solve', 'name' => 'First Touch', 'description' => 'Complete your first puzzle', 'icon' => "\u{1F331}", 'sort_order' => 1],
            ['slug' => 'first_daily', 'name' => 'Daily Ritual', 'description' => 'Complete your first daily puzzle', 'icon' => "\u{2600}\u{FE0F}", 'sort_order' => 2],

            // Volume
            ['slug' => 'solve_5', 'name' => 'Getting Started', 'description' => 'Complete 5 puzzles', 'icon' => "\u{1F3AF}", 'sort_order' => 3],
            ['slug' => 'solve_25', 'name' => 'Dedicated', 'description' => 'Complete 25 puzzles', 'icon' => "\u{1F4AA}", 'sort_order' => 4],
            ['slug' => 'solve_100', 'name' => 'Century', 'description' => 'Complete 100 puzzles', 'icon' => "\u{1F4AF}", 'sort_order' => 5],

            // Streaks
            ['slug' => 'streak_3', 'name' => 'On a Roll', 'description' => '3-day daily streak', 'icon' => "\u{1F525}", 'sort_order' => 6],
            ['slug' => 'streak_7', 'name' => 'Week Warrior', 'description' => '7-day daily streak', 'icon' => "\u{26A1}", 'sort_order' => 7],
            ['slug' => 'streak_30', 'name' => 'Monthly Master', 'description' => '30-day daily streak', 'icon' => "\u{1F3C6}", 'sort_order' => 8],

            // Clean solves
            ['slug' => 'no_hints', 'name' => 'Pure Logic', 'description' => 'Solve 10 puzzles without using hints', 'icon' => "\u{1F9E0}", 'sort_order' => 9],
            ['slug' => 'no_checks', 'name' => 'Confident', 'description' => 'Solve 10 puzzles without checking errors', 'icon' => "\u{1F3B2}", 'sort_order' => 10],
            ['slug' => 'perfect', 'name' => 'Flawless', 'description' => 'Complete 3 puzzles with no hints and no checks', 'icon' => "\u{2728}", 'sort_order' => 11],
            ['slug' => 'five_perfect', 'name' => 'Perfectionist', 'description' => '15 flawless solves', 'icon' => "\u{1F48E}", 'sort_order' => 12],

            // Speed
            ['slug' => 'speed_3min', 'name' => 'Quick Thinker', 'description' => 'Solve any 8x8 puzzle (or larger) in under 3 minutes', 'icon' => "\u{1F3C3}", 'sort_order' => 13],
            ['slug' => 'speed_1min', 'name' => 'Lightning', 'description' => 'Solve a 6x6 puzzle in under 1 minute', 'icon' => "\u{26A1}", 'sort_order' => 14],
            ['slug' => 'speed_run', 'name' => 'Speed Run', 'description' => 'Solve 3 puzzles each in under 3 minutes', 'icon' => "\u{1F680}", 'sort_order' => 15],

            // Pack mastery
            ['slug' => 'pack_complete', 'name' => 'Pack Master', 'description' => 'Complete 20 puzzles from regular packs', 'icon' => "\u{1F4E6}", 'sort_order' => 16],

            // Grid size milestones
            ['slug' => 'first_12x12', 'name' => 'Bigger Challenge', 'description' => 'Solve your first 12x12 puzzle', 'icon' => "\u{1F532}", 'sort_order' => 17],
            ['slug' => 'first_14x14', 'name' => 'Giant Board', 'description' => 'Solve your first 14x14 puzzle', 'icon' => "\u{1F5FC}", 'sort_order' => 18],
            ['slug' => 'size_master', 'name' => 'Size Matters', 'description' => 'Solve at least one puzzle in every grid size (6x6, 8x8, 10x10, 12x12, 14x14)', 'icon' => "\u{1F4D0}", 'sort_order' => 19],

            // Daily dedication
            ['slug' => 'daily_30', 'name' => 'Daily Regular', 'description' => 'Complete 30 daily puzzles', 'icon' => "\u{1F4C5}", 'sort_order' => 20],
            ['slug' => 'daily_100', 'name' => 'Daily Devotee', 'description' => 'Complete 100 daily puzzles', 'icon' => "\u{1F5D3}\u{FE0F}", 'sort_order' => 21],

            // Time of day
            ['slug' => 'night_owl', 'name' => 'Night Owl', 'description' => 'Solve a puzzle between midnight and 5 AM', 'icon' => "\u{1F989}", 'sort_order' => 22],
            ['slug' => 'early_bird', 'name' => 'Early Bird', 'description' => 'Complete a daily puzzle before 7 AM', 'icon' => "\u{1F426}", 'sort_order' => 23],

            // Session
            ['slug' => 'marathon', 'name' => 'Marathon', 'description' => 'Solve 5 puzzles in a single day', 'icon' => "\u{1F3C5}", 'sort_order' => 24],
            ['slug' => 'comeback', 'name' => 'Comeback', 'description' => 'Return to play after at least 7 days away', 'icon' => "\u{1F504}", 'sort_order' => 25],
        ];

        foreach ($achievements as $data) {
            AchievementDefinition::updateOrCreate(
                ['game_id' => $game->id, 'slug' => $data['slug']],
                $data
            );
        }
    }
}
