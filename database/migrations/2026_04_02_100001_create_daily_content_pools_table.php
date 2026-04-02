<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_content_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('pool_key', 100);
            $table->json('content');
            $table->date('starts_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['game_id', 'pool_key']);
            $table->index(['game_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_content_pools');
    }
};
