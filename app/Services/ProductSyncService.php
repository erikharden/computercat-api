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

                // Try to set pricing/availability on existing products too
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

                // Upload review screenshot if configured and not already present
                $notes = array_merge($notes, $this->maybeUploadScreenshot($client, $iapId, $product));

                $product->update([
                    'apple_state' => 'synced',
                    'apple_synced_at' => now(),
                ]);

                $extra = $notes ? ' ('.implode(', ', $notes).')' : '';

                return [
                    'success' => true,
                    'action' => 'already_exists',
                    'message' => "Already exists (id: {$iapId}){$extra}.",
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

            // Upload review screenshot if configured
            $screenshotNotes = $this->maybeUploadScreenshot($client, $iapId, $product);
            $screenshotNote = $screenshotNotes ? ' '.implode(' ', $screenshotNotes).'.' : '';

            $product->update([
                'apple_state' => 'synced',
                'apple_synced_at' => now(),
            ]);

            return [
                'success' => true,
                'action' => 'created',
                'message' => "Created (id: {$iapId}) with price {$product->price} {$product->currency} and worldwide availability.{$availabilityNote}{$priceNote}{$screenshotNote}",
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
     * Upload the product's review screenshot to App Store Connect if:
     *   - review_screenshot_path is set on the product
     *   - the file exists on disk
     *   - the IAP doesn't already have a screenshot attached
     *
     * @return array<string> Status notes to include in the sync response.
     */
    private function maybeUploadScreenshot(AppStoreConnectClient $client, string $iapId, \App\Models\Product $product): array
    {
        if (! $product->review_screenshot_path) {
            return [];
        }

        // Resolve path: absolute path or relative to storage/app/private
        $path = $product->review_screenshot_path;
        if (! str_starts_with($path, '/')) {
            $path = storage_path('app/private/'.ltrim($path, '/'));
        }

        if (! file_exists($path)) {
            return ["screenshot not found at {$path}"];
        }

        // Check existing screenshot state; if it's in FAILED state or present,
        // handle it appropriately
        try {
            $existing = $client->getReviewScreenshot($iapId);
            if ($existing) {
                $state = $existing['attributes']['assetDeliveryState']['state'] ?? null;

                if ($state === 'COMPLETE') {
                    return ['screenshot already uploaded'];
                }

                // Failed, uploading, or any non-complete state — delete and retry
                try {
                    $client->deleteReviewScreenshot($existing['id']);
                } catch (\Throwable $e) {
                    // If we can't delete, try uploading anyway
                }
            }
        } catch (\Throwable $e) {
            // Non-fatal — proceed with upload attempt
        }

        try {
            $client->uploadReviewScreenshot($iapId, $path);

            return ['screenshot uploaded'];
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Screenshot already exists') || str_contains($e->getMessage(), 'MEDIA_ASSET_CREATION_NOT_ALLOWED')) {
                return ['screenshot already uploaded'];
            }

            return ['screenshot upload failed: '.$e->getMessage()];
        }
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
