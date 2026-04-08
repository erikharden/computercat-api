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
                    // Repeater format: indexed array of {product_id, type, id?}
                    'product_grants' => [
                        ['product_id' => 'tocco_pack_6x6_medium', 'type' => 'pack', 'id' => '6x6-medium'],
                        ['product_id' => 'tocco_pack_6x6_hard', 'type' => 'pack', 'id' => '6x6-hard'],
                        ['product_id' => 'tocco_pack_8x8_medium', 'type' => 'pack', 'id' => '8x8-medium'],
                        ['product_id' => 'tocco_pack_8x8_hard', 'type' => 'pack', 'id' => '8x8-hard'],
                        ['product_id' => 'tocco_pack_10x10_medium', 'type' => 'pack', 'id' => '10x10-medium'],
                        ['product_id' => 'tocco_pack_10x10_hard', 'type' => 'pack', 'id' => '10x10-hard'],
                        ['product_id' => 'tocco_pack_12x12_medium', 'type' => 'pack', 'id' => '12x12-medium'],
                        ['product_id' => 'tocco_pack_12x12_hard', 'type' => 'pack', 'id' => '12x12-hard'],
                        ['product_id' => 'tocco_pack_14x14_medium', 'type' => 'pack', 'id' => '14x14-medium'],
                        ['product_id' => 'tocco_pack_14x14_hard', 'type' => 'pack', 'id' => '14x14-hard'],
                        ['product_id' => 'tocco_themes_cozy', 'type' => 'theme_pack', 'id' => 'cozy'],
                        ['product_id' => 'tocco_themes_dark_side', 'type' => 'theme_pack', 'id' => 'dark-side'],
                        ['product_id' => 'tocco_themes_pop_culture', 'type' => 'theme_pack', 'id' => 'pop-culture'],
                        ['product_id' => 'tocco_themes_wanderlust', 'type' => 'theme_pack', 'id' => 'wanderlust'],
                        ['product_id' => 'tocco_themes_art_house', 'type' => 'theme_pack', 'id' => 'art-house'],
                        ['product_id' => 'tocco_themes_wild_card', 'type' => 'theme_pack', 'id' => 'wild-card'],
                        ['product_id' => 'tocco_themes_upgraded', 'type' => 'theme_pack', 'id' => 'upgraded'],
                        ['product_id' => 'tocco_supporter', 'type' => 'supporter'],
                    ],
                    'theme_packs' => [
                        'cozy' => ['botanical', 'terracotta', 'safari', 'amalfi', 'diner', 'cottage'],
                        'dark-side' => ['noir', 'cinema', 'steampunk', 'medieval', 'sheriff'],
                        'pop-culture' => ['comic', 'popart', 'platformer', 'superhero', 'sport'],
                        'wanderlust' => ['miami', 'tropicana', 'vegas', 'desert', 'circus'],
                        'art-house' => ['artdeco', 'ukiyo', 'origami', 'vinyl', 'gemstone'],
                        'wild-card' => ['laboratory', 'timetravel', 'rock', 'pirate', 'neon'],
                        'upgraded' => ['zen-v2', 'candy-v2', 'retro-v2', 'space-v2', 'mono-v2', 'ocean-v2'],
                    ],
                ],
                'is_active' => true,
            ]
        );
    }
}
