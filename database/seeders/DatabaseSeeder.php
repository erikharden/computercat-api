<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user for Filament
        User::factory()->create([
            'name' => 'Admin',
            'display_name' => 'Admin',
            'email' => 'admin@computercat.cc',
            'password' => bcrypt('password'),
            'is_anonymous' => false,
        ]);

        $this->call([
            GameSeeder::class,
            ToccoLeaderboardSeeder::class,
            ToccoAchievementSeeder::class,
        ]);
    }
}
