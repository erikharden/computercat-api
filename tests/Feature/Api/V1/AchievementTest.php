<?php

namespace Tests\Feature\Api\V1;

use App\Models\AchievementDefinition;
use App\Models\Game;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AchievementTest extends TestCase
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
        ]);

        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------
    // List definitions (index)
    // -------------------------------------------------------

    public function test_list_achievement_definitions_returns_active_sorted_by_sort_order(): void
    {
        Sanctum::actingAs($this->user);

        $third = AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'third',
            'name' => 'Third',
            'description' => 'Third achievement',
            'icon' => 'icon-third',
            'sort_order' => 3,
            'is_secret' => false,
            'is_active' => true,
        ]);

        $first = AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'first',
            'name' => 'First',
            'description' => 'First achievement',
            'icon' => 'icon-first',
            'sort_order' => 1,
            'is_secret' => false,
            'is_active' => true,
        ]);

        $second = AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'second',
            'name' => 'Second',
            'description' => 'Second achievement',
            'icon' => 'icon-second',
            'sort_order' => 2,
            'is_secret' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/games/tocco/achievements');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.slug', 'first')
            ->assertJsonPath('data.1.slug', 'second')
            ->assertJsonPath('data.2.slug', 'third');
    }

    public function test_list_achievements_excludes_inactive_definitions(): void
    {
        Sanctum::actingAs($this->user);

        AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'active-one',
            'name' => 'Active',
            'description' => 'An active achievement',
            'icon' => 'icon-active',
            'sort_order' => 1,
            'is_secret' => false,
            'is_active' => true,
        ]);

        AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'inactive-one',
            'name' => 'Inactive',
            'description' => 'An inactive achievement',
            'icon' => 'icon-inactive',
            'sort_order' => 2,
            'is_secret' => false,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/games/tocco/achievements');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'active-one');
    }

    // -------------------------------------------------------
    // Report unlocks (store)
    // -------------------------------------------------------

    public function test_report_unlocks_returns_201_with_newly_unlocked(): void
    {
        Sanctum::actingAs($this->user);

        AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'first-win',
            'name' => 'First Win',
            'description' => 'Win your first game',
            'icon' => 'icon-win',
            'sort_order' => 1,
            'is_secret' => false,
            'is_active' => true,
        ]);

        AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'speed-demon',
            'name' => 'Speed Demon',
            'description' => 'Complete in under 30s',
            'icon' => 'icon-speed',
            'sort_order' => 2,
            'is_secret' => false,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/games/tocco/achievements', [
            'slugs' => ['first-win', 'speed-demon'],
        ]);

        $response->assertStatus(201)
            ->assertJsonCount(2, 'unlocked')
            ->assertJsonPath('already_unlocked', 0);

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_report_unlocks_is_idempotent(): void
    {
        Sanctum::actingAs($this->user);

        $definition = AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'first-win',
            'name' => 'First Win',
            'description' => 'Win your first game',
            'icon' => 'icon-win',
            'sort_order' => 1,
            'is_secret' => false,
            'is_active' => true,
        ]);

        // First call — should unlock
        $this->postJson('/api/v1/games/tocco/achievements', [
            'slugs' => ['first-win'],
        ])->assertStatus(201)
            ->assertJsonCount(1, 'unlocked')
            ->assertJsonPath('already_unlocked', 0);

        // Second call — already unlocked
        $response = $this->postJson('/api/v1/games/tocco/achievements', [
            'slugs' => ['first-win'],
        ]);

        $response->assertStatus(201)
            ->assertJsonCount(0, 'unlocked')
            ->assertJsonPath('already_unlocked', 1);

        // Only one record in DB
        $this->assertDatabaseCount('user_achievements', 1);
    }

    public function test_report_unlocks_ignores_unknown_slugs(): void
    {
        Sanctum::actingAs($this->user);

        AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'first-win',
            'name' => 'First Win',
            'description' => 'Win your first game',
            'icon' => 'icon-win',
            'sort_order' => 1,
            'is_secret' => false,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/games/tocco/achievements', [
            'slugs' => ['first-win', 'nonexistent-slug'],
        ]);

        $response->assertStatus(201)
            ->assertJsonCount(1, 'unlocked')
            ->assertJsonPath('already_unlocked', 0);
    }

    public function test_report_unlocks_ignores_inactive_definitions(): void
    {
        Sanctum::actingAs($this->user);

        AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'retired-badge',
            'name' => 'Retired Badge',
            'description' => 'No longer available',
            'icon' => 'icon-retired',
            'sort_order' => 1,
            'is_secret' => false,
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/games/tocco/achievements', [
            'slugs' => ['retired-badge'],
        ]);

        $response->assertStatus(201)
            ->assertJsonCount(0, 'unlocked')
            ->assertJsonPath('already_unlocked', 0);

        $this->assertDatabaseCount('user_achievements', 0);
    }

    // -------------------------------------------------------
    // My achievements (me)
    // -------------------------------------------------------

    public function test_get_my_achievements_returns_unlocked_with_definition(): void
    {
        Sanctum::actingAs($this->user);

        $definition = AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'first-win',
            'name' => 'First Win',
            'description' => 'Win your first game',
            'icon' => 'icon-win',
            'sort_order' => 1,
            'is_secret' => false,
            'is_active' => true,
        ]);

        UserAchievement::create([
            'user_id' => $this->user->id,
            'achievement_definition_id' => $definition->id,
            'unlocked_at' => now(),
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/games/tocco/achievements/me');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'first-win')
            ->assertJsonPath('data.0.name', 'First Win')
            ->assertJsonStructure([
                'data' => [
                    ['slug', 'name', 'description', 'icon', 'unlocked_at'],
                ],
            ]);
    }

    public function test_get_my_achievements_returns_empty_when_none_unlocked(): void
    {
        Sanctum::actingAs($this->user);

        AchievementDefinition::create([
            'game_id' => $this->game->id,
            'slug' => 'first-win',
            'name' => 'First Win',
            'description' => 'Win your first game',
            'icon' => 'icon-win',
            'sort_order' => 1,
            'is_secret' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/games/tocco/achievements/me');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    // -------------------------------------------------------
    // Auth & validation
    // -------------------------------------------------------

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/games/tocco/achievements');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/games/tocco/achievements/me');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/games/tocco/achievements', [
            'slugs' => ['first-win'],
        ]);
        $response->assertStatus(401);
    }

    public function test_invalid_game_returns_404(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/games/nonexistent-game/achievements');

        $response->assertStatus(404);
    }
}
