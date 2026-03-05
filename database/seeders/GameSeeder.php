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
                ],
                'is_active' => true,
            ]
        );
    }
}
