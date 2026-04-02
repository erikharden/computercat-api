<?php

namespace Tests\Feature\Api\V1;

use App\Models\Game;
use App\Models\PlayerStreak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StreakTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();

        $this->game = Game::create([
            'slug' => 'tocco',
            'name' => 'Tocco',
            'is_active' => true,
            'settings' => [
                'streaks' => [
                    'daily' => [
                        'max_freezes' => 3,
                        'freeze_earn_interval' => 7,
                    ],
                ],
            ],
        ]);
    }

    public function test_get_streak_returns_zero_when_no_streak(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/games/tocco/streaks/daily');

        $response->assertOk()
            ->assertJsonPath('data.streak_key', 'daily')
            ->assertJsonPath('data.current_streak', 0)
            ->assertJsonPath('data.longest_streak', 0);
    }

    public function test_record_first_activity_sets_streak_to_1(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/games/tocco/streaks/daily/record');

        $response->assertOk()
            ->assertJsonPath('data.current_streak', 1)
            ->assertJsonPath('data.longest_streak', 1)
            ->assertJsonPath('data.last_activity_date', now()->toDateString());
    }

    public function test_record_same_day_is_idempotent(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/games/tocco/streaks/daily/record');
        $response = $this->postJson('/api/v1/games/tocco/streaks/daily/record');

        $response->assertOk()
            ->assertJsonPath('data.current_streak', 1);

        $this->assertDatabaseCount('player_streaks', 1);
    }

    public function test_record_consecutive_day_increments_streak(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $today = Carbon::create(2026, 4, 2, 12, 0, 0);
        $yesterday = Carbon::create(2026, 4, 1, 12, 0, 0);

        // Record yesterday
        Carbon::setTestNow($yesterday);
        $this->postJson('/api/v1/games/tocco/streaks/daily/record');

        // Record today
        Carbon::setTestNow($today);
        $response = $this->postJson('/api/v1/games/tocco/streaks/daily/record');

        $response->assertOk()
            ->assertJsonPath('data.current_streak', 2)
            ->assertJsonPath('data.longest_streak', 2);
    }

    public function test_record_with_gap_resets_streak(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Record 3 days ago
        Carbon::setTestNow(Carbon::now()->subDays(3));
        $this->postJson('/api/v1/games/tocco/streaks/daily/record');

        // Record today — gap too large, reset
        Carbon::setTestNow(Carbon::now());
        $response = $this->postJson('/api/v1/games/tocco/streaks/daily/record');

        $response->assertOk()
            ->assertJsonPath('data.current_streak', 1)
            ->assertJsonPath('data.longest_streak', 1);
    }

    public function test_freeze_used_when_missing_one_day(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a streak with a freeze balance
        PlayerStreak::create([
            'user_id' => $user->id,
            'game_id' => $this->game->id,
            'streak_key' => 'daily',
            'current_streak' => 5,
            'longest_streak' => 5,
            'last_activity_date' => Carbon::now()->subDays(2)->toDateString(),
            'freeze_balance' => 1,
        ]);

        $response = $this->postJson('/api/v1/games/tocco/streaks/daily/record');

        $response->assertOk()
            ->assertJsonPath('data.current_streak', 6)
            ->assertJsonPath('data.freeze_balance', 0);
    }

    public function test_no_freeze_available_resets_streak(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        PlayerStreak::create([
            'user_id' => $user->id,
            'game_id' => $this->game->id,
            'streak_key' => 'daily',
            'current_streak' => 5,
            'longest_streak' => 5,
            'last_activity_date' => Carbon::now()->subDays(2)->toDateString(),
            'freeze_balance' => 0,
        ]);

        $response = $this->postJson('/api/v1/games/tocco/streaks/daily/record');

        $response->assertOk()
            ->assertJsonPath('data.current_streak', 1)
            ->assertJsonPath('data.longest_streak', 5);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/games/tocco/streaks/daily');

        $response->assertStatus(401);
    }
}
