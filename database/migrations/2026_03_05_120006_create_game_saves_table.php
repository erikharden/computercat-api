<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_saves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('save_key', 50)->default('main');
            $table->json('data');
            $table->integer('version')->default(1);
            $table->string('checksum', 64)->nullable();
            $table->timestamp('saved_at');
            $table->timestamps();

            $table->unique(['user_id', 'game_id', 'save_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_saves');
    }
};
