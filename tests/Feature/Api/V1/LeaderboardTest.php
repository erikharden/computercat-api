<?php

namespace Tests\Feature\Api\V1;

use App\Models\Game;
use App\Models\LeaderboardEntry;
use App\Models\LeaderboardType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;

    private LeaderboardType $ascType;

    private LeaderboardType $descType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->game = Game::create([
            'slug' => 'tocco',
            'name' => 'Tocco',
            'is_active' => true,
            'settings' => [
                'anti_cheat' => [
                    'min_score' => 5,
                    'max_score' => 86400,
                ],
            ],
        ]);

        $this->ascType = LeaderboardType::create([
            'game_id' => $this->game->id,
            'slug' => 'daily-time',
            'name' => 'Daily Time',
            'sort_direction' => 'asc',
            'score_label' => 'seconds',
            'period' => 'daily',
            'max_entries_per_period' => 100,
            'is_active' => true,
        ]);

        $this->descType = LeaderboardType::create([
            'game_id' => $this->game->id,
            'slug' => 'daily-score',
            'name' => 'Daily Score',
            'sort_direction' => 'desc',
            'score_label' => 'points',
            'period' => 'daily',
            'max_entries_per_period' => 100,
            'is_active' => true,
        ]);
    }

    // -------------------------------------------------------
    // Submit score
    // -------------------------------------------------------

    public function test_submit_score_returns_201_with_entry_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/games/tocco/leaderboards/daily-time', [
            'score' => 120,
            'metadata' => ['puzzle_id' => 'abc'],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['rank', 'score', 'metadata', 'submitted_at', 'user' => ['id', 'display_name']],
            ])
            ->assertJsonPath('data.score', 120)
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertDatabaseHas('leaderboard_entries', [
            'leaderboard_type_id' => $this->ascType->id,
            'user_id' => $user->id,
            'score' => 120,
        ]);
    }

    public function test_submit_score_keeps_best_score_for_asc_leaderboard(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // First submission: 200 seconds
        $this->postJson('/api/v1/games/tocco/leaderboards/daily-time', [
            'score' => 200,
        ])->assertStatus(201);

        // Second submission: 100 seconds (better for asc)
        $this->postJson('/api/v1/games/tocco/leaderboards/daily-time', [
            'score' => 100,
        ])->assertStatus(201);

        $this->assertDatabaseCount('leaderboard_entries', 1);
        $this->assertDatabaseHas('leaderboard_entries', [
            'user_id' => $user->id,
            'leaderboard_type_id' => $this->ascType->id,
            'score' => 100,
        ]);
    }

    public function test_submit_score_does_not_replace_better_score(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // First submission: 100 seconds
        $this->postJson('/api/v1/games/tocco/leaderboards/daily-time', [
            'score' => 100,
        ])->assertStatus(201);

        // Second submission: 200 seconds (worse for asc)
        $this->postJson('/api/v1/games/tocco/leaderboards/daily-time', [
            'score' => 200,
        ])->assertStatus(201);

        $this->assertDatabaseCount('leaderboard_entries', 1);
        $this->assertDatabaseHas('leaderboard_entries', [
            'user_id' => $user->id,
            'leaderboard_type_id' => $this->ascType->id,
            'score' => 100,
        ]);
    }

    // -------------------------------------------------------
    // Get leaderboard
    // -------------------------------------------------------

    public function test_get_leaderboard_sorted_asc(): void
    {
        $users = User::factory()->count(3)->create();
        Sanctum::actingAs($users[0]);

        $periodKey = now()->format('Y-m-d');

        foreach ([300, 100, 200] as $i => $score) {
            LeaderboardEntry::create([
                'leaderboard_type_id' => $this->ascType->id,
                'user_id' => $users[$i]->id,
                'period_key' => $periodKey,
                'score' => $score,
                'metadata' => null,
                'submitted_at' => now(),
            ]);
        }

        $response = $this->getJson('/api/v1/games/tocco/leaderboards/daily-time');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(3, $data);
        $this->assertEquals(100, $data[0]['score']);
        $this->assertEquals(200, $data[1]['score']);
        $this->assertEquals(300, $data[2]['score']);
    }

    public function test_get_leaderboard_returns_entries_with_rank(): void
    {
        $users = User::factory()->count(2)->create();
        Sanctum::actingAs($users[0]);

        $periodKey = now()->format('Y-m-d');

        LeaderboardEntry::create([
            'leaderboard_type_id' => $this->ascType->id,
            'user_id' => $users[0]->id,
            'period_key' => $periodKey,
            'score' => 50,
            'metadata' => null,
            'submitted_at' => now(),
        ]);

        LeaderboardEntry::create([
            'leaderboard_type_id' => $this->ascType->id,
            'user_id' => $users[1]->id,
            'period_key' => $periodKey,
            'score' => 100,
            'metadata' => null,
            'submitted_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/games/tocco/leaderboards/daily-time');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals(1, $data[0]['rank']);
        $this->assertEquals(2, $data[1]['rank']);
    }

    // -------------------------------------------------------
    // Get my rank
    // -------------------------------------------------------

    public function test_get_my_rank_returns_rank_and_entry(): void
    {
        $users = User::factory()->count(3)->create();
        Sanctum::actingAs($users[1]);

        $periodKey = now()->format('Y-m-d');

        // User 0: best score (50), User 1: middle (100), User 2: worst (200)
        foreach ([50, 100, 200] as $i => $score) {
            LeaderboardEntry::create([
                'leaderboard_type_id' => $this->ascType->id,
                'user_id' => $users[$i]->id,
                'period_key' => $periodKey,
                'score' => $score,
                'metadata' => null,
                'submitted_at' => now(),
            ]);
        }

        $response = $this->getJson('/api/v1/games/tocco/leaderboards/daily-time/me');

        $response->assertOk()
            ->assertJsonPath('rank', 2)
            ->assertJsonPath('data.score', 100)
            ->assertJsonPath('data.user.id', $users[1]->id);
    }

    public function test_get_my_rank_returns_null_when_no_entry(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/games/tocco/leaderboards/daily-time/me');

        $response->assertOk()
            ->assertJsonPath('data', null);
    }

    // -------------------------------------------------------
    // Specific period
    // -------------------------------------------------------

    public function test_get_specific_period_leaderboard(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $pastPeriodKey = '2025-01-01';

        LeaderboardEntry::create([
            'leaderboard_type_id' => $this->ascType->id,
            'user_id' => $user->id,
            'period_key' => $pastPeriodKey,
            'score' => 60,
            'metadata' => null,
            'submitted_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/games/tocco/leaderboards/daily-time/{$pastPeriodKey}");

        $response->assertOk()
            ->assertJsonPath('period_key', $pastPeriodKey)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.score', 60);
    }

    // -------------------------------------------------------
    // Anti-cheat
    // -------------------------------------------------------

    public function test_anticheat_rejects_negative_score(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/games/tocco/leaderboards/daily-score', [
            'score' => -1,
        ]);

        $response->assertStatus(422);
    }

    public function test_anticheat_rejects_implausibly_low_time_for_asc(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/games/tocco/leaderboards/daily-time', [
            'score' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Score is implausibly low.');
    }

    public function test_anticheat_rejects_implausibly_high_time_for_asc(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/games/tocco/leaderboards/daily-time', [
            'score' => 86401,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Score exceeds maximum allowed value.');
    }

    // -------------------------------------------------------
    // Error cases
    // -------------------------------------------------------

    public function test_invalid_leaderboard_type_slug_returns_404(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/games/tocco/leaderboards/nonexistent-type');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Leaderboard type not found.');
    }

    public function test_invalid_game_slug_returns_404(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/games/nonexistent-game/leaderboards/daily-time');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Game not found.');
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/games/tocco/leaderboards/daily-time');

        $response->assertStatus(401);
    }
}
