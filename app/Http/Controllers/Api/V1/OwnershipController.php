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
 * Product grants and theme pack mappings are stored in games.settings.
 *
 * Supports two formats for product_grants:
 *   Array format (from Filament Repeater):
 *     [{"product_id": "pack_6x6", "type": "pack", "id": "6x6-medium"}, ...]
 *   Map format (from seeders):
 *     {"pack_6x6": {"type": "pack", "id": "6x6-medium"}, ...}
 *
 * Theme packs can be:
 *   Array: {"retro-nature": ["retro", "spring"]}
 *   String: {"retro-nature": "retro,spring"} (comma-separated from KeyValue)
 */
class OwnershipController extends Controller
{
    public function show(Request $request, Game $game): JsonResponse
    {
        $settings = $game->settings ?? [];
        $rawGrants = $settings['product_grants'] ?? [];
        $rawThemePacks = $settings['theme_packs'] ?? [];

        // Normalize product_grants to a productId → grant map
        $productGrants = $this->normalizeGrants($rawGrants);

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
                $themes = $this->resolveThemePack($grant['id'], $rawThemePacks);
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

    /**
     * Normalize product_grants from either array or map format.
     */
    private function normalizeGrants(array $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        // Check if it's an indexed array (Repeater format)
        if (array_is_list($raw)) {
            $map = [];
            foreach ($raw as $entry) {
                $pid = $entry['product_id'] ?? null;
                if ($pid) {
                    $map[$pid] = $entry;
                }
            }

            return $map;
        }

        // Already a map (seeder format)
        return $raw;
    }

    /**
     * Resolve theme pack ID to individual theme IDs.
     * Handles both array and comma-separated string formats.
     */
    private function resolveThemePack(string $packId, array $themePacks): array
    {
        $themes = $themePacks[$packId] ?? [];

        if (is_string($themes)) {
            return array_map('trim', explode(',', $themes));
        }

        return is_array($themes) ? $themes : [];
    }
}
