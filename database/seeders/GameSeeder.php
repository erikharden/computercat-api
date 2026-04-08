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
                        'min_score' => 5_000,
                        'max_score' => 86_400_000,
                    ],
                    'streaks' => [
                        'daily' => [
                            'max_freezes' => 5,
                            'freeze_earn_interval' => 7, // earn 1 freeze per 7-day milestone
                        ],
                    ],
                    // Products are now managed in the products table
                    // (see ToccoProductSeeder). theme_packs is kept here because
                    // it defines what each theme pack CONTAINS, which is game
                    // content rather than store metadata.
                    // KeyValue format: {pack_id: "comma,separated,theme,ids"}
                    'theme_packs' => [
                        'cozy' => 'botanical,terracotta,safari,amalfi,diner,cottage',
                        'dark-side' => 'noir,cinema,steampunk,medieval,sheriff',
                        'pop-culture' => 'comic,popart,platformer,superhero,sport',
                        'wanderlust' => 'miami,tropicana,vegas,desert,circus',
                        'art-house' => 'artdeco,ukiyo,origami,vinyl,gemstone',
                        'wild-card' => 'laboratory,timetravel,rock,pirate,neon',
                        'upgraded' => 'zen-v2,candy-v2,retro-v2,space-v2,mono-v2,ocean-v2',
                    ],
                ],
                'is_active' => true,
            ]
        );
    }
}
