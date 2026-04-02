<?php

namespace Tests\Feature\Api\V1;

use App\Models\DailyContentPool;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DailyContentTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;

    private DailyContentPool $pool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->game = Game::create([
            'slug' => 'tocco',
            'name' => 'Tocco',
            'is_active' => true,
            'settings' => [],
        ]);

        $this->pool = DailyContentPool::create([
            'game_id' => $this->game->id,
            'pool_key' => 'daily-puzzle',
            'content' => ['puzzle-a', 'puzzle-b', 'puzzle-c', 'puzzle-d', 'puzzle-e'],
            'is_active' => true,
        ]);
    }

    public function test_get_today_content_returns_deterministic_item(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/games/tocco/daily/daily-puzzle');

        $response->assertOk()
            ->assertJsonStructure(['data', 'date', 'pool_key'])
            ->assertJsonPath('pool_key', 'daily-puzzle')
            ->assertJsonPath('date', now()->toDateString());

        // Same request should return same content (deterministic)
        $response2 = $this->getJson('/api/v1/games/tocco/daily/daily-puzzle');
        $this->assertEquals($response->json('data'), $response2->json('data'));
    }

    public function test_get_past_date_content_returns_item(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/games/tocco/daily/daily-puzzle/2025-06-15');

        $response->assertOk()
            ->assertJsonPath('date', '2025-06-15');
    }

    public function test_get_future_date_content_is_forbidden(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $futureDate = now()->addDay()->toDateString();
        $response = $this->getJson("/api/v1/games/tocco/daily/daily-puzzle/{$futureDate}");

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Cannot request future dates.');
    }

    public function test_nonexistent_pool_returns_404(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/games/tocco/daily/nonexistent');

        $response->assertStatus(404);
    }

    public function test_inactive_pool_returns_404(): void
    {
        $this->pool->update(['is_active' => false]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/games/tocco/daily/daily-puzzle');

        $response->assertStatus(404);
    }

    public function test_pool_with_future_starts_at_returns_404(): void
    {
        $this->pool->update(['starts_at' => now()->addWeek()]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/games/tocco/daily/daily-puzzle');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/games/tocco/daily/daily-puzzle');

        $response->assertStatus(401);
    }
}
