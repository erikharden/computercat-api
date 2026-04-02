<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('streak_key', 100);
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('longest_streak')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->unsignedInteger('freeze_balance')->default(0);
            $table->date('last_freeze_date')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'game_id', 'streak_key'], 'player_streak_unique');
            $table->index(['game_id', 'streak_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_streaks');
    }
};
