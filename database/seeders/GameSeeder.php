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
                    'product_grants' => [
                        'pack_6x6_medium' => ['type' => 'pack', 'id' => '6x6-medium'],
                        'pack_6x6_hard' => ['type' => 'pack', 'id' => '6x6-hard'],
                        'pack_8x8_medium' => ['type' => 'pack', 'id' => '8x8-medium'],
                        'pack_8x8_hard' => ['type' => 'pack', 'id' => '8x8-hard'],
                        'pack_10x10_medium' => ['type' => 'pack', 'id' => '10x10-medium'],
                        'pack_10x10_hard' => ['type' => 'pack', 'id' => '10x10-hard'],
                        'pack_12x12_medium' => ['type' => 'pack', 'id' => '12x12-medium'],
                        'pack_12x12_hard' => ['type' => 'pack', 'id' => '12x12-hard'],
                        'pack_14x14_medium' => ['type' => 'pack', 'id' => '14x14-medium'],
                        'pack_14x14_hard' => ['type' => 'pack', 'id' => '14x14-hard'],
                        'theme_retro_nature' => ['type' => 'theme_pack', 'id' => 'retro-nature'],
                        'theme_classic_plus' => ['type' => 'theme_pack', 'id' => 'classic-plus'],
                        'theme_noir_mystery' => ['type' => 'theme_pack', 'id' => 'noir-mystery'],
                        'theme_art_pop' => ['type' => 'theme_pack', 'id' => 'art-pop'],
                        'theme_sci_fi' => ['type' => 'theme_pack', 'id' => 'sci-fi'],
                        'theme_nature' => ['type' => 'theme_pack', 'id' => 'nature'],
                        'theme_vibes' => ['type' => 'theme_pack', 'id' => 'vibes'],
                        'supporter' => ['type' => 'supporter'],
                    ],
                    'theme_packs' => [
                        'retro-nature' => ['retro', 'spring', 'ocean', 'space'],
                        'classic-plus' => ['zen-v2', 'candy-v2', 'retro-v2', 'space-v2'],
                        'noir-mystery' => ['noir', 'pirate', 'steampunk', 'cinema', 'comic'],
                        'art-pop' => ['popart', 'artdeco', 'ukiyo', 'origami', 'vinyl'],
                        'sci-fi' => ['timetravel', 'platformer', 'superhero', 'laboratory', 'gemstone'],
                        'nature' => ['botanical', 'safari', 'terracotta', 'desert', 'fika', 'tropicana', 'amalfi'],
                        'vibes' => ['miami', 'rock', 'diner', 'circus', 'vegas', 'sheriff', 'sport', 'medieval'],
                    ],
                ],
                'is_active' => true,
            ]
        );
    }
}
