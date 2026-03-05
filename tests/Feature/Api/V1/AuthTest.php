<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // Anonymous
    // -------------------------------------------------------

    public function test_can_create_anonymous_user(): void
    {
        $response = $this->postJson('/api/v1/auth/anonymous');

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user']);

        $this->assertDatabaseHas('users', [
            'is_anonymous' => true,
        ]);
    }

    public function test_can_create_anonymous_user_with_display_name(): void
    {
        $response = $this->postJson('/api/v1/auth/anonymous', [
            'display_name' => 'GuestPlayer',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user']);

        $this->assertDatabaseHas('users', [
            'display_name' => 'GuestPlayer',
            'is_anonymous' => true,
        ]);
    }

    // -------------------------------------------------------
    // Me (profile)
    // -------------------------------------------------------

    public function test_can_get_profile_when_authenticated(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
    }

    public function test_get_profile_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------
    // Update display_name
    // -------------------------------------------------------

    public function test_can_update_display_name(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/auth/me', [
            'display_name' => 'NewName',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'display_name' => 'NewName',
        ]);
    }

    public function test_update_display_name_fails_when_too_long(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/auth/me', [
            'display_name' => str_repeat('a', 51),
        ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------
    // Login
    // -------------------------------------------------------

    public function test_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_returns_403_for_banned_user(): void
    {
        $user = User::factory()->create([
            'email' => 'banned@example.com',
            'password' => 'password',
            'is_banned' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'banned@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------
    // Register
    // -------------------------------------------------------

    public function test_authenticated_anonymous_user_can_register_to_upgrade_account(): void
    {
        $anonymous = User::factory()->create([
            'is_anonymous' => true,
        ]);
        Sanctum::actingAs($anonymous);

        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'upgraded@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'id' => $anonymous->id,
            'email' => 'upgraded@example.com',
            'is_anonymous' => false,
        ]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $user = User::factory()->create(['is_anonymous' => true]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'taken@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_weak_password(): void
    {
        $user = User::factory()->create(['is_anonymous' => true]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'new@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------
    // Delete account
    // -------------------------------------------------------

    public function test_can_delete_account(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/auth/me');

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }
}
