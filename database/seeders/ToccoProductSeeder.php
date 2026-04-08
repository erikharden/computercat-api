<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ToccoProductSeeder extends Seeder
{
    public function run(): void
    {
        $game = Game::where('slug', 'tocco')->firstOrFail();

        $products = [
            // ── Puzzle packs (10 × 12 kr) ────────────────────────────────────
            // Apple limits: name ≤ 30 chars, description ≤ 55 chars
            [
                'product_id' => 'tocco_pack_6x6_medium',
                'reference_name' => 'Tocco 6x6 Medium Pack',
                'grant_type' => 'pack',
                'grant_id' => '6x6-medium',
                'price' => 12,
                'display_name' => '6×6 Medium Pack',
                'description' => '20 medium 6×6 Binairo puzzles.',
                'sort_order' => 10,
            ],
            [
                'product_id' => 'tocco_pack_6x6_hard',
                'reference_name' => 'Tocco 6x6 Hard Pack',
                'grant_type' => 'pack',
                'grant_id' => '6x6-hard',
                'price' => 12,
                'display_name' => '6×6 Hard Pack',
                'description' => '20 hard 6×6 Binairo puzzles.',
                'sort_order' => 11,
            ],
            [
                'product_id' => 'tocco_pack_8x8_medium',
                'reference_name' => 'Tocco 8x8 Medium Pack',
                'grant_type' => 'pack',
                'grant_id' => '8x8-medium',
                'price' => 12,
                'display_name' => '8×8 Medium Pack',
                'description' => '20 medium 8×8 Binairo puzzles.',
                'sort_order' => 20,
            ],
            [
                'product_id' => 'tocco_pack_8x8_hard',
                'reference_name' => 'Tocco 8x8 Hard Pack',
                'grant_type' => 'pack',
                'grant_id' => '8x8-hard',
                'price' => 12,
                'display_name' => '8×8 Hard Pack',
                'description' => '20 hard 8×8 Binairo puzzles.',
                'sort_order' => 21,
            ],
            [
                'product_id' => 'tocco_pack_10x10_medium',
                'reference_name' => 'Tocco 10x10 Medium Pack',
                'grant_type' => 'pack',
                'grant_id' => '10x10-medium',
                'price' => 12,
                'display_name' => '10×10 Medium Pack',
                'description' => '20 medium 10×10 Binairo puzzles.',
                'sort_order' => 30,
            ],
            [
                'product_id' => 'tocco_pack_10x10_hard',
                'reference_name' => 'Tocco 10x10 Hard Pack',
                'grant_type' => 'pack',
                'grant_id' => '10x10-hard',
                'price' => 12,
                'display_name' => '10×10 Hard Pack',
                'description' => '20 hard 10×10 Binairo puzzles.',
                'sort_order' => 31,
            ],
            [
                'product_id' => 'tocco_pack_12x12_medium',
                'reference_name' => 'Tocco 12x12 Medium Pack',
                'grant_type' => 'pack',
                'grant_id' => '12x12-medium',
                'price' => 12,
                'display_name' => '12×12 Medium Pack',
                'description' => '20 medium 12×12 Binairo puzzles.',
                'sort_order' => 40,
            ],
            [
                'product_id' => 'tocco_pack_12x12_hard',
                'reference_name' => 'Tocco 12x12 Hard Pack',
                'grant_type' => 'pack',
                'grant_id' => '12x12-hard',
                'price' => 12,
                'display_name' => '12×12 Hard Pack',
                'description' => '20 hard 12×12 Binairo puzzles.',
                'sort_order' => 41,
            ],
            [
                'product_id' => 'tocco_pack_14x14_medium',
                'reference_name' => 'Tocco 14x14 Medium Pack',
                'grant_type' => 'pack',
                'grant_id' => '14x14-medium',
                'price' => 12,
                'display_name' => '14×14 Medium Pack',
                'description' => '20 medium 14×14 Binairo puzzles.',
                'sort_order' => 50,
            ],
            [
                'product_id' => 'tocco_pack_14x14_hard',
                'reference_name' => 'Tocco 14x14 Hard Pack',
                'grant_type' => 'pack',
                'grant_id' => '14x14-hard',
                'price' => 12,
                'display_name' => '14×14 Hard Pack',
                'description' => '20 hard 14×14 Binairo puzzles.',
                'sort_order' => 51,
            ],

            // ── Theme packs ──────────────────────────────────────────────────
            [
                'product_id' => 'tocco_themes_cozy',
                'reference_name' => 'Tocco Cozy Theme Pack',
                'grant_type' => 'theme_pack',
                'grant_id' => 'cozy',
                'price' => 25,
                'display_name' => 'Cozy Themes',
                'description' => '6 warm themes for relaxed puzzle sessions.',
                'sort_order' => 100,
            ],
            [
                'product_id' => 'tocco_themes_dark_side',
                'reference_name' => 'Tocco Dark Side Theme Pack',
                'grant_type' => 'theme_pack',
                'grant_id' => 'dark-side',
                'price' => 19,
                'display_name' => 'Dark Side Themes',
                'description' => '5 moody, atmospheric themes.',
                'sort_order' => 101,
            ],
            [
                'product_id' => 'tocco_themes_pop_culture',
                'reference_name' => 'Tocco Pop Culture Theme Pack',
                'grant_type' => 'theme_pack',
                'grant_id' => 'pop-culture',
                'price' => 19,
                'display_name' => 'Pop Culture Themes',
                'description' => '5 pop-inspired themes.',
                'sort_order' => 102,
            ],
            [
                'product_id' => 'tocco_themes_wanderlust',
                'reference_name' => 'Tocco Wanderlust Theme Pack',
                'grant_type' => 'theme_pack',
                'grant_id' => 'wanderlust',
                'price' => 19,
                'display_name' => 'Wanderlust Themes',
                'description' => '5 travel-inspired themes.',
                'sort_order' => 103,
            ],
            [
                'product_id' => 'tocco_themes_art_house',
                'reference_name' => 'Tocco Art House Theme Pack',
                'grant_type' => 'theme_pack',
                'grant_id' => 'art-house',
                'price' => 19,
                'display_name' => 'Art House Themes',
                'description' => '5 artistic, refined themes.',
                'sort_order' => 104,
            ],
            [
                'product_id' => 'tocco_themes_wild_card',
                'reference_name' => 'Tocco Wild Card Theme Pack',
                'grant_type' => 'theme_pack',
                'grant_id' => 'wild-card',
                'price' => 19,
                'display_name' => 'Wild Card Themes',
                'description' => '5 offbeat, unexpected themes.',
                'sort_order' => 105,
            ],
            [
                'product_id' => 'tocco_themes_upgraded',
                'reference_name' => 'Tocco Upgraded Theme Pack',
                'grant_type' => 'theme_pack',
                'grant_id' => 'upgraded',
                'price' => 19,
                'display_name' => 'Upgraded Themes',
                'description' => '6 refreshed classic themes.',
                'sort_order' => 106,
            ],

            // ── Supporter ────────────────────────────────────────────────────
            [
                'product_id' => 'tocco_supporter',
                'reference_name' => 'Tocco Supporter',
                'grant_type' => 'supporter',
                'grant_id' => null,
                'price' => 179,
                'display_name' => 'Tocco Supporter',
                'description' => 'Unlock everything — all packs and themes.',
                'sort_order' => 200,
            ],
        ];

        foreach ($products as $data) {
            Product::updateOrCreate(
                ['game_id' => $game->id, 'product_id' => $data['product_id']],
                array_merge($data, [
                    'product_type' => 'non_consumable',
                    'currency' => 'SEK',
                    'is_active' => true,
                ])
            );
        }
    }
}
