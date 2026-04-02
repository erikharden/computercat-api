<?php

namespace Tests\Feature\Api\V1;

use App\Models\AchievementDefinition;
use App\Models\Game;
use App\Models\LeaderboardEntry;
use App\Models\LeaderboardType;
use App\Models\PlayerStreak;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlayerStatsTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->game = Game::create([
            'slug' => 'tocco',
            'name' => 'Tocco',
            'is_active' => true,
            'settings' => [],
        ]);

        $this->user = User::factory()->create();
    }

    public function test_get_stats_returns_aggregated_data(): void
    {
        Sanctum::actingAs($this->user);

        $type = LeaderboardType::create([
            'game_id' => $this->game->id,
            'slug' => 'daily-time',
            'name' => 'Daily Time',
            'sort_direction' => 'asc',
            'score_label' => 'seconds',
            'period' => 'daily',
            'max_entries_per_period' => 100,
            'is_active' => true,
        ]);

        LeaderboardEntry::create([
            'leaderboard_type_id' => $type->id,
            'user_id' => $this->user->id,
            'period_key' => '2025-06-01',
            'score' => 120,
            'submitted_at' => now(),
        ]);

        LeaderboardEntry::create([
            'leaderboard_type_id' => $type->id,
            'user_id' => $this->user->id,
            'period_key' => '2025-06-02',
            'score' => 90,
            'submitted_at' => now(),
        ]);

        $def = AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'first-win',
            'name' => 'First Win',
            'description' => 'Win your first game',
            'icon' => 'trophy',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        UserAchievement::create([
            'user_id' => $this->user->id,
            'achievement_definition_id' => $def->id,
            'unlocked_at' => now(),
            'created_at' => now(),
        ]);

        PlayerStreak::create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'streak_key' => 'daily',
            'current_streak' => 5,
            'longest_streak' => 10,
            'last_activity_date' => now()->toDateString(),
            'freeze_balance' => 0,
        ]);

        $response = $this->getJson('/api/v1/games/tocco/stats/me');

        $response->assertOk()
            ->assertJsonPath('data.total_games', 2)
            ->assertJsonPath('data.best_scores.daily-time', 90)
            ->assertJsonPath('data.achievement_count', 1)
            ->assertJsonPath('data.current_streaks.daily', 5)
            ->assertJsonStructure([
                'data' => ['total_games', 'best_scores', 'achievement_count', 'current_streaks', 'member_since', 'last_active'],
            ]);
    }

    public function test_get_stats_returns_empty_for_new_user(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/games/tocco/stats/me');

        $response->assertOk()
            ->assertJsonPath('data.total_games', 0)
            ->assertJsonPath('data.best_scores', [])
            ->assertJsonPath('data.achievement_count', 0);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/games/tocco/stats/me');

        $response->assertStatus(401);
    }
}
