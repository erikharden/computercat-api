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
 * Product grants and theme pack mappings are stored in games.settings
 * so each game can define its own products without code changes.
 *
 * Expected settings format:
 * {
 *   "product_grants": {
 *     "pack_6x6_medium": { "type": "pack", "id": "6x6-medium" },
 *     "theme_retro_nature": { "type": "theme_pack", "id": "retro-nature" },
 *     "supporter": { "type": "supporter" }
 *   },
 *   "theme_packs": {
 *     "retro-nature": ["retro", "spring", "ocean", "space"]
 *   }
 * }
 */
class OwnershipController extends Controller
{
    public function show(Request $request, Game $game): JsonResponse
    {
        $settings = $game->settings ?? [];
        $productGrants = $settings['product_grants'] ?? [];
        $themePacks = $settings['theme_packs'] ?? [];

        $purchases = Purchase::where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->where('status', '!=', 'refunded')
            ->pluck('product_id')
            ->toArray();

        $ownedPacks = [];
        $ownedThemes = [];
        $isSupporter = false;

        foreach ($purchases as $productId) {
            $grant = $productGrants[$productId] ?? null;
            if (! $grant) {
                continue;
            }

            $type = $grant['type'] ?? null;

            if ($type === 'pack' && isset($grant['id'])) {
                $ownedPacks[] = $grant['id'];
            } elseif ($type === 'theme_pack' && isset($grant['id'])) {
                $themes = $themePacks[$grant['id']] ?? [];
                $ownedThemes = array_merge($ownedThemes, $themes);
            } elseif ($type === 'supporter') {
                $isSupporter = true;
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
