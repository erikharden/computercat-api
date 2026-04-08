<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Product;
use App\Services\AppStoreConnectClient;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class SyncIapCommand extends Command
{
    protected $signature = 'iap:sync
        {game : Game slug}
        {--dry-run : Show what would be synced without making API calls}';

    protected $description = 'Sync products from the database to App Store Connect';

    public function handle(): int
    {
        $game = Game::where('slug', $this->argument('game'))->first();
        if (! $game) {
            $this->error("Game '{$this->argument('game')}' not found");

            return self::FAILURE;
        }

        $products = Product::where('game_id', $game->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($products->isEmpty()) {
            $this->warn('No active products to sync');

            return self::SUCCESS;
        }

        $this->info("Syncing {$products->count()} products for {$game->name}");

        if ($this->option('dry-run')) {
            $this->line('DRY RUN — no API calls will be made');
            foreach ($products as $p) {
                $this->line("  • {$p->product_id} ({$p->grant_type}) — {$p->price} {$p->currency}");
            }

            return self::SUCCESS;
        }

        try {
            $client = new AppStoreConnectClient($game);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($products as $product) {
            $this->line("\n→ {$product->product_id}");

            try {
                $product->update(['apple_state' => 'syncing', 'apple_sync_error' => null]);

                // Check if IAP already exists
                $existing = $client->findInAppPurchase($product->product_id);

                if ($existing) {
                    $this->line("  ℹ already exists in App Store Connect (id: {$existing['id']})");
                    $iapId = $existing['id'];
                    $skipped++;
                } else {
                    // Create new IAP
                    $created = $client->createInAppPurchase([
                        'name' => $product->reference_name,
                        'productId' => $product->product_id,
                        'inAppPurchaseType' => $this->mapProductType($product->product_type),
                        'reviewNote' => $product->review_notes ?? '',
                    ]);
                    $iapId = $created['id'];
                    $this->info("  ✓ created (id: {$iapId})");

                    // Add English localization
                    $client->createLocalization(
                        $iapId,
                        'en-US',
                        $product->display_name,
                        $product->description,
                    );
                    $this->info('  ✓ added en-US localization');
                    $created++;
                }

                $product->update([
                    'apple_state' => 'synced',
                    'apple_synced_at' => now(),
                ]);
            } catch (Throwable $e) {
                $this->error('  ✗ '.$e->getMessage());
                $product->update([
                    'apple_state' => 'failed',
                    'apple_sync_error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $this->line('');
        $this->info("Done: {$created} created, {$skipped} already existed, {$failed} failed");

        if ($created > 0 || $skipped > 0) {
            $this->line('');
            $this->warn('⚠  Manual steps still required in App Store Connect:');
            $this->line('   1. Set PRICE for each product (dropdown from Apple\'s price tiers)');
            $this->line('   2. Upload REVIEW SCREENSHOT for each product (can reuse same image)');
            $this->line('');
            $this->line("   Until these are done, products will show 'Missing Metadata' in App Store Connect.");
            $this->line('');

            // List expected prices for quick reference
            $this->line('   Expected prices from database:');
            foreach ($products as $p) {
                $this->line(sprintf('     %-30s %6.2f %s', $p->product_id, $p->price, $p->currency));
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Map our product_type enum to Apple's inAppPurchaseType.
     */
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
