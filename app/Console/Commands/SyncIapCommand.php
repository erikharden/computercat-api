<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Product;
use App\Services\ProductSyncService;
use Illuminate\Console\Command;

class SyncIapCommand extends Command
{
    protected $signature = 'iap:sync
        {game : Game slug}
        {--dry-run : Show what would be synced without making API calls}';

    protected $description = 'Sync products from the database to App Store Connect';

    public function handle(ProductSyncService $service): int
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

        $created = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($products as $product) {
            $this->line("\n→ {$product->product_id}");
            $result = $service->sync($product);

            if ($result['success']) {
                if ($result['action'] === 'already_exists') {
                    $this->line('  ℹ '.$result['message']);
                    $skipped++;
                } else {
                    $this->info('  ✓ '.$result['message']);
                    $created++;
                }
            } else {
                $this->error('  ✗ '.$result['message']);
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
            $this->line('   Expected prices from database:');
            foreach ($products as $p) {
                $this->line(sprintf('     %-30s %6.2f %s', $p->product_id, $p->price, $p->currency));
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
