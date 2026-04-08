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
                $iapId = $existing['id'];

                // Try to set pricing/availability on existing products too —
                // they might have been created before this feature was added
                $notes = [];
                try {
                    $client->createAvailability($iapId);
                    $notes[] = 'availability set';
                } catch (RuntimeException $e) {
                    if ($e->getCode() !== 409 && ! str_contains($e->getMessage(), 'DUPLICATE')) {
                        $notes[] = 'availability failed';
                    }
                }

                try {
                    $baseTerritory = $this->territoryForCurrency($product->currency);
                    $pricePointId = $client->findPricePoint($iapId, $baseTerritory, (float) $product->price);
                    if ($pricePointId) {
                        $client->createPriceSchedule($iapId, $baseTerritory, $pricePointId);
                        $notes[] = "price set ({$product->price} {$product->currency})";
                    }
                } catch (RuntimeException $e) {
                    if ($e->getCode() !== 409 && ! str_contains($e->getMessage(), 'DUPLICATE')) {
                        $notes[] = 'price failed: '.$e->getMessage();
                    }
                }

                $product->update([
                    'apple_state' => 'synced',
                    'apple_synced_at' => now(),
                ]);

                $extra = $notes ? ' ('.implode(', ', $notes).')' : '';

                return [
                    'success' => true,
                    'action' => 'already_exists',
                    'message' => "Already exists (id: {$iapId}){$extra}. Only review screenshot is still manual.",
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

            // Set availability (all territories)
            $availabilityNote = '';
            try {
                $client->createAvailability($iapId);
            } catch (RuntimeException $e) {
                if ($e->getCode() !== 409 && ! str_contains($e->getMessage(), 'DUPLICATE')) {
                    $availabilityNote = ' Availability setup failed: '.$e->getMessage();
                }
            }

            // Set price schedule using Swedish territory as base
            $priceNote = '';
            try {
                $baseTerritory = $this->territoryForCurrency($product->currency);
                $pricePointId = $client->findPricePoint($iapId, $baseTerritory, (float) $product->price);

                if ($pricePointId) {
                    $client->createPriceSchedule($iapId, $baseTerritory, $pricePointId);
                } else {
                    $priceNote = " No matching Apple price tier found for {$product->price} {$product->currency} — set price manually.";
                }
            } catch (RuntimeException $e) {
                if ($e->getCode() === 409 || str_contains($e->getMessage(), 'DUPLICATE')) {
                    // Price schedule already exists
                } else {
                    $priceNote = ' Price setup failed: '.$e->getMessage();
                }
            }

            $product->update([
                'apple_state' => 'synced',
                'apple_synced_at' => now(),
            ]);

            return [
                'success' => true,
                'action' => 'created',
                'message' => "Created in App Store Connect (id: {$iapId}) with price {$product->price} {$product->currency} and worldwide availability.{$availabilityNote}{$priceNote} Review screenshot still needs to be uploaded manually.",
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

    /**
     * Map ISO currency to Apple's 3-letter territory code for pricing base.
     */
    private function territoryForCurrency(string $currency): string
    {
        return match (strtoupper($currency)) {
            'SEK' => 'SWE',
            'USD' => 'USA',
            'EUR' => 'DEU', // Germany as default EUR
            'GBP' => 'GBR',
            'NOK' => 'NOR',
            'DKK' => 'DNK',
            default => 'USA',
        };
    }
}
