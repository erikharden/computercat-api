<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 100);
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('event_type', ['leaderboard', 'challenge', 'seasonal']);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['game_id', 'slug']);
            $table->index(['game_id', 'is_active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_events');
    }
};
