<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Server-authoritative ownership check.
 *
 * The client should call this on app boot to get the definitive list
 * of what the user owns. This prevents localStorage tampering from
 * granting access to premium content.
 */
class OwnershipController extends Controller
{
    /**
     * Product ID → what it grants.
     * This is the source of truth for what each purchase unlocks.
     */
    private const PRODUCT_GRANTS = [
        // Puzzle packs
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

        // Theme packs
        'theme_retro_nature' => ['type' => 'theme_pack', 'id' => 'retro-nature'],
        'theme_classic_plus' => ['type' => 'theme_pack', 'id' => 'classic-plus'],
        'theme_noir_mystery' => ['type' => 'theme_pack', 'id' => 'noir-mystery'],
        'theme_art_pop' => ['type' => 'theme_pack', 'id' => 'art-pop'],
        'theme_sci_fi' => ['type' => 'theme_pack', 'id' => 'sci-fi'],
        'theme_nature' => ['type' => 'theme_pack', 'id' => 'nature'],
        'theme_vibes' => ['type' => 'theme_pack', 'id' => 'vibes'],

        // Supporter
        'supporter' => ['type' => 'supporter'],
    ];

    /**
     * Theme pack ID → theme IDs it contains.
     * Mirrors THEME_PACKS from the client.
     */
    private const THEME_PACK_THEMES = [
        'retro-nature' => ['retro', 'spring', 'ocean', 'space'],
        'classic-plus' => ['zen-v2', 'candy-v2', 'retro-v2', 'space-v2'],
        'noir-mystery' => ['noir', 'pirate', 'steampunk', 'cinema', 'comic'],
        'art-pop' => ['popart', 'artdeco', 'ukiyo', 'origami', 'vinyl'],
        'sci-fi' => ['timetravel', 'platformer', 'superhero', 'laboratory', 'gemstone'],
        'nature' => ['botanical', 'safari', 'terracotta', 'desert', 'fika', 'tropicana', 'amalfi'],
        'vibes' => ['miami', 'rock', 'diner', 'circus', 'vegas', 'sheriff', 'sport', 'medieval'],
    ];

    public function show(Request $request, Game $game): JsonResponse
    {
        $purchases = Purchase::where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->where('status', '!=', 'refunded')
            ->pluck('product_id')
            ->toArray();

        $ownedPacks = [];
        $ownedThemes = [];
        $isSupporter = false;

        foreach ($purchases as $productId) {
            $grant = self::PRODUCT_GRANTS[$productId] ?? null;
            if (! $grant) {
                continue;
            }

            switch ($grant['type']) {
                case 'pack':
                    $ownedPacks[] = $grant['id'];
                    break;
                case 'theme_pack':
                    $themes = self::THEME_PACK_THEMES[$grant['id']] ?? [];
                    $ownedThemes = array_merge($ownedThemes, $themes);
                    break;
                case 'supporter':
                    $isSupporter = true;
                    break;
            }
        }

        return response()->json([
            'data' => [
                'owned_packs' => array_values(array_unique($ownedPacks)),
                'owned_themes' => array_values(array_unique($ownedThemes)),
                'is_supporter' => $isSupporter,
            ],
        ]);
    }
}
