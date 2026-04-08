<?php

namespace App\Services;

use App\Models\Product;
use RuntimeException;
use Throwable;

/**
 * Syncs a single product to App Store Connect.
 *
 * Creates the IAP + English localization if it doesn't exist.
 * Price and review screenshot still require manual steps in App Store Connect.
 */
class ProductSyncService
{
    /**
     * Sync a product. Safe to call repeatedly — idempotent.
     *
     * @return array{success: bool, action: string, message: string}
     */
    public function sync(Product $product): array
    {
        $game = $product->game;

        // Check if App Store Connect is configured for this game
        $acConfig = $game->settings['app_store_connect'] ?? [];
        if (empty($acConfig['issuer_id']) || empty($acConfig['key_id']) || empty($acConfig['private_key']) || empty($acConfig['app_apple_id'])) {
            return [
                'success' => false,
                'action' => 'skipped',
                'message' => 'App Store Connect API not configured for this game. Add credentials in Game settings.',
            ];
        }

        $product->update(['apple_state' => 'syncing', 'apple_sync_error' => null]);

        try {
            $client = new AppStoreConnectClient($game);

            // Check if IAP already exists
            $existing = $client->findInAppPurchase($product->product_id);

            if ($existing) {
                $product->update([
                    'apple_state' => 'synced',
                    'apple_synced_at' => now(),
                ]);

                return [
                    'success' => true,
                    'action' => 'already_exists',
                    'message' => "Already exists in App Store Connect (id: {$existing['id']}). Remember to set price and upload review screenshot manually.",
                ];
            }

            // Create new IAP shell
            try {
                $created = $client->createInAppPurchase([
                    'name' => $product->reference_name,
                    'productId' => $product->product_id,
                    'inAppPurchaseType' => $this->mapProductType($product->product_type),
                    'reviewNote' => $product->review_notes ?? '',
                ]);
                $iapId = $created['id'];
            } catch (RuntimeException $e) {
                // 409 Conflict: product ID already exists in App Store Connect but
                // our findInAppPurchase() didn't catch it (filter quirks, deleted
                // products blocking IDs, etc.). Mark as synced anyway since Apple
                // already has the record.
                if ($e->getCode() === 409 || str_contains($e->getMessage(), 'DUPLICATE') || str_contains($e->getMessage(), 'already been used')) {
                    $product->update([
                        'apple_state' => 'synced',
                        'apple_synced_at' => now(),
                    ]);

                    return [
                        'success' => true,
                        'action' => 'already_exists',
                        'message' => "Product ID '{$product->product_id}' already exists in App Store Connect (possibly in a different state or deleted). No changes made. If you need to update it, edit it directly in App Store Connect.",
                    ];
                }
                throw $e;
            }

            // Add English localization
            try {
                $client->createLocalization(
                    $iapId,
                    'en-US',
                    $product->display_name,
                    $product->description,
                );
            } catch (RuntimeException $e) {
                // Localization may already exist — not fatal
                if ($e->getCode() !== 409 && ! str_contains($e->getMessage(), 'DUPLICATE')) {
                    throw $e;
                }
            }

            $product->update([
                'apple_state' => 'synced',
                'apple_synced_at' => now(),
            ]);

            return [
                'success' => true,
                'action' => 'created',
                'message' => "Created in App Store Connect (id: {$iapId}). Now set price ({$product->price} {$product->currency}) and upload review screenshot manually.",
            ];
        } catch (RuntimeException $e) {
            $product->update([
                'apple_state' => 'failed',
                'apple_sync_error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'action' => 'failed',
                'message' => $e->getMessage(),
            ];
        } catch (Throwable $e) {
            $product->update([
                'apple_state' => 'failed',
                'apple_sync_error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'action' => 'failed',
                'message' => "Unexpected error: {$e->getMessage()}",
            ];
        }
    }

    private function mapProductType(string $type): string
    {
        return match ($type) {
            'non_consumable' => 'NON_CONSUMABLE',
            'consumable' => 'CONSUMABLE',
            'subscription' => 'AUTO_RENEWABLE_SUBSCRIPTION',
            default => 'NON_CONSUMABLE',
        };
    }
}
