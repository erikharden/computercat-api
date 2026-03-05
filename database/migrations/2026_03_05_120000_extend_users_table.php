<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name', 50)->after('name')->nullable();
            $table->string('apple_id')->nullable()->unique()->after('email');
            $table->string('google_id')->nullable()->unique()->after('apple_id');
            $table->boolean('is_anonymous')->default(true)->after('google_id');
            $table->boolean('is_banned')->default(false)->after('is_anonymous');
            $table->timestamp('last_seen_at')->nullable()->after('is_banned');

            // Make email nullable for anonymous users
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'apple_id', 'google_id', 'is_anonymous', 'is_banned', 'last_seen_at']);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
