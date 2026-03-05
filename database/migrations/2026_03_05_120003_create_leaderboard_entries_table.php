<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leaderboard_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period_key', 20);
            $table->bigInteger('score');
            $table->json('metadata')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['leaderboard_type_id', 'user_id', 'period_key'], 'leaderboard_entry_unique');
            $table->index(['leaderboard_type_id', 'period_key', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
    }
};
