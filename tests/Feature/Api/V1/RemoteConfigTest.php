<?php

namespace Tests\Feature\Api\V1;

use App\Models\Game;
use App\Models\RemoteConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoteConfigTest extends TestCase
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

    public function test_get_config_returns_all_active_configs(): void
    {
        RemoteConfig::create([
            'game_id' => $this->game->id,
            'key' => 'feature_hints',
            'value' => 'true',
            'value_type' => 'bool',
            'is_active' => true,
        ]);

        RemoteConfig::create([
            'game_id' => $this->game->id,
            'key' => 'max_lives',
            'value' => '5',
            'value_type' => 'int',
            'is_active' => true,
        ]);

        RemoteConfig::create([
            'game_id' => $this->game->id,
            'key' => 'disabled_feature',
            'value' => 'hidden',
            'value_type' => 'string',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/games/tocco/config');

        $response->assertOk()
            ->assertJsonPath('data.feature_hints', true)
            ->assertJsonPath('data.max_lives', 5)
            ->assertJsonMissing(['disabled_feature']);
    }

    public function test_get_config_is_public_no_auth_needed(): void
    {
        // No Sanctum::actingAs — this should work without auth
        $response = $this->getJson('/api/v1/games/tocco/config');

        $response->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_get_config_with_json_value(): void
    {
        RemoteConfig::create([
            'game_id' => $this->game->id,
            'key' => 'theme_colors',
            'value' => '{"primary":"#ff0000","secondary":"#00ff00"}',
            'value_type' => 'json',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/games/tocco/config');

        $response->assertOk()
            ->assertJsonPath('data.theme_colors.primary', '#ff0000');
    }

    public function test_invalid_game_returns_404(): void
    {
        $response = $this->getJson('/api/v1/games/nonexistent/config');

        $response->assertStatus(404);
    }
}
