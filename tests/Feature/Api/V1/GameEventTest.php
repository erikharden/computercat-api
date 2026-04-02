<?php

namespace Tests\Feature\Api\V1;

use App\Models\Game;
use App\Models\GameEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GameEventTest extends TestCase
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
            'settings' => [],
        ]);
    }

    public function test_list_active_events(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        GameEvent::create([
            'game_id' => $this->game->id,
            'slug' => 'spring-challenge',
            'name' => 'Spring Challenge',
            'event_type' => 'challenge',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'is_active' => true,
        ]);

        GameEvent::create([
            'game_id' => $this->game->id,
            'slug' => 'past-event',
            'name' => 'Past Event',
            'event_type' => 'seasonal',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subWeek(),
            'is_active' => true,
        ]);

        GameEvent::create([
            'game_id' => $this->game->id,
            'slug' => 'future-event',
            'name' => 'Future Event',
            'event_type' => 'leaderboard',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/games/tocco/events');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'spring-challenge');
    }

    public function test_get_single_event_by_slug(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        GameEvent::create([
            'game_id' => $this->game->id,
            'slug' => 'spring-challenge',
            'name' => 'Spring Challenge',
            'description' => 'A great challenge',
            'event_type' => 'challenge',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/games/tocco/events/spring-challenge');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'spring-challenge')
            ->assertJsonPath('data.name', 'Spring Challenge')
            ->assertJsonPath('data.event_type', 'challenge');
    }

    public function test_get_nonexistent_event_returns_404(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/games/tocco/events/nonexistent');

        $response->assertStatus(404);
    }

    public function test_inactive_event_not_in_list(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        GameEvent::create([
            'game_id' => $this->game->id,
            'slug' => 'disabled',
            'name' => 'Disabled Event',
            'event_type' => 'challenge',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/games/tocco/events');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/games/tocco/events');

        $response->assertStatus(401);
    }
}
