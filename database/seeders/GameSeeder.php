<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        Game::updateOrCreate(
            ['slug' => 'tocco'],
            [
                'name' => 'Tocco',
                'description' => 'A premium Binairo logic puzzle game. Fill the grid with two symbols following three simple rules.',
                'settings' => [
                    'grid_sizes' => [6, 8, 10, 12, 14],
                    'difficulties' => ['easy', 'medium', 'hard', 'expert'],
                    'symbols' => ['circle', 'cross'],
                    'anti_cheat' => [
                        'score_unit' => 'ms',
                        'min_score' => 5_000,      // 5 seconds in ms
                        'max_score' => 86_400_000,  // 24 hours in ms
                    ],
                ],
                'is_active' => true,
            ]
        );
    }
}
