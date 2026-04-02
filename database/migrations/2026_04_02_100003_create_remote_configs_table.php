<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remote_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);
            $table->text('value');
            $table->enum('value_type', ['string', 'int', 'bool', 'json']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['game_id', 'key']);
            $table->index(['game_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remote_configs');
    }
};
