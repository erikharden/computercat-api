<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();

            // Store-level identity
            $table->string('product_id', 100); // matches App Store / Play Store ID
            $table->string('reference_name', 100); // internal name shown in stores admin
            $table->string('product_type', 20)->default('non_consumable'); // non_consumable, consumable, subscription

            // Grant (what buying this unlocks in-game)
            $table->string('grant_type', 30); // pack, theme_pack, supporter, custom
            $table->string('grant_id', 100)->nullable(); // pack id, theme_pack id, etc.

            // Pricing (base currency — Apple derives others from pricing tiers)
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('SEK');

            // Localized display (for now, one locale — can expand later)
            $table->string('display_name', 100);
            $table->text('description');
            $table->text('review_notes')->nullable();
            $table->string('review_screenshot_path')->nullable();

            // Ordering and activation
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            // Sync state with stores
            $table->string('apple_state', 20)->default('pending'); // pending, syncing, synced, failed
            $table->text('apple_sync_error')->nullable();
            $table->timestamp('apple_synced_at')->nullable();

            $table->timestamps();

            $table->unique(['game_id', 'product_id']);
            $table->index(['game_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
