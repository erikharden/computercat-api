<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin users for Filament
        $admins = [
            ['name' => 'Erik', 'email' => 'erik@humblebrag.se', 'password' => env('ADMIN_ERIK_PASS', 'password')],
            ['name' => 'Mattias', 'email' => 'mattias@humblebrag.se', 'password' => env('ADMIN_MATTIAS_PASS', 'password')],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'display_name' => $admin['name'],
                    'password' => bcrypt($admin['password']),
                    'is_anonymous' => false,
                ]
            );
        }

        $this->call([
            GameSeeder::class,
            ToccoLeaderboardSeeder::class,
            ToccoAchievementSeeder::class,
            ToccoProductSeeder::class,
        ]);
    }
}
