<?php

namespace Tests\Feature\Api\V1;

use App\Models\Game;
use App\Models\GameSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GameSaveTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->game = Game::create([
            'slug' => 'tocco',
            'name' => 'Tocco',
            'is_active' => true,
        ]);
    }

    // -------------------------------------------------------
    // Create save
    // -------------------------------------------------------

    public function test_create_save_returns_version_1(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/games/tocco/saves/main', [
            'data' => ['level' => 1, 'score' => 0],
            'version' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.save_key', 'main')
            ->assertJsonPath('data.version', 1)
            ->assertJsonPath('data.data.level', 1);

        $this->assertDatabaseHas('game_saves', [
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'save_key' => 'main',
            'version' => 1,
        ]);
    }

    // -------------------------------------------------------
    // Update save with correct version
    // -------------------------------------------------------

    public function test_update_save_with_correct_version_increments_version(): void
    {
        Sanctum::actingAs($this->user);

        $save = GameSave::create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'save_key' => 'main',
            'data' => ['level' => 1],
            'version' => 1,
            'saved_at' => now(),
        ]);

        $response = $this->putJson('/api/v1/games/tocco/saves/main', [
            'data' => ['level' => 2, 'score' => 100],
            'version' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.data.level', 2);
    }

    // -------------------------------------------------------
    // Update save with wrong version — 409 conflict
    // -------------------------------------------------------

    public function test_update_save_with_wrong_version_returns_409(): void
    {
        Sanctum::actingAs($this->user);

        $save = GameSave::create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'save_key' => 'main',
            'data' => ['level' => 3],
            'version' => 3,
            'saved_at' => now(),
        ]);

        $response = $this->putJson('/api/v1/games/tocco/saves/main', [
            'data' => ['level' => 4],
            'version' => 1,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('server_version', 3)
            ->assertJsonStructure(['message', 'server_version', 'data']);
    }

    // -------------------------------------------------------
    // Get save
    // -------------------------------------------------------

    public function test_get_save_returns_data(): void
    {
        Sanctum::actingAs($this->user);

        GameSave::create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'save_key' => 'main',
            'data' => ['level' => 5],
            'version' => 2,
            'checksum' => 'abc123',
            'saved_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/games/tocco/saves/main');

        $response->assertStatus(200)
            ->assertJsonPath('data.save_key', 'main')
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.data.level', 5)
            ->assertJsonPath('data.checksum', 'abc123');
    }

    // -------------------------------------------------------
    // Get non-existent save — 404
    // -------------------------------------------------------

    public function test_get_nonexistent_save_returns_404(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/games/tocco/saves/missing');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------
    // List saves
    // -------------------------------------------------------

    public function test_list_saves_returns_user_saves_for_game(): void
    {
        Sanctum::actingAs($this->user);

        GameSave::create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'save_key' => 'main',
            'data' => ['level' => 1],
            'version' => 1,
            'saved_at' => now(),
        ]);

        GameSave::create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'save_key' => 'backup',
            'data' => ['level' => 2],
            'version' => 1,
            'saved_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/games/tocco/saves');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    // -------------------------------------------------------
    // Delete save
    // -------------------------------------------------------

    public function test_delete_save_returns_success(): void
    {
        Sanctum::actingAs($this->user);

        GameSave::create([
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'save_key' => 'main',
            'data' => ['level' => 1],
            'version' => 1,
            'saved_at' => now(),
        ]);

        $response = $this->deleteJson('/api/v1/games/tocco/saves/main');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Save deleted.');

        $this->assertDatabaseMissing('game_saves', [
            'user_id' => $this->user->id,
            'game_id' => $this->game->id,
            'save_key' => 'main',
        ]);
    }

    // -------------------------------------------------------
    // Delete non-existent save — 404
    // -------------------------------------------------------

    public function test_delete_nonexistent_save_returns_404(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/games/tocco/saves/missing');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------
    // User isolation — can't see another user's saves
    // -------------------------------------------------------

    public function test_user_cannot_see_another_users_saves(): void
    {
        $otherUser = User::factory()->create();

        GameSave::create([
            'user_id' => $otherUser->id,
            'game_id' => $this->game->id,
            'save_key' => 'main',
            'data' => ['level' => 99],
            'version' => 5,
            'saved_at' => now(),
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/games/tocco/saves/main');
        $response->assertStatus(404);

        $response = $this->getJson('/api/v1/games/tocco/saves');
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    // -------------------------------------------------------
    // Unauthenticated — 401
    // -------------------------------------------------------

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/games/tocco/saves');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/games/tocco/saves/main');
        $response->assertStatus(401);

        $response = $this->putJson('/api/v1/games/tocco/saves/main', [
            'data' => ['level' => 1],
            'version' => 0,
        ]);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/v1/games/tocco/saves/main');
        $response->assertStatus(401);
    }
}
