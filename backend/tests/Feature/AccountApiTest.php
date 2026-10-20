<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_use_their_access_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Nadia Pinterus',
            'username' => 'nadia_home',
            'email' => 'nadia@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', 'nadia_home')
            ->assertJsonMissingPath('data.user.password');

        $token = $response->json('data.token');
        $userId = $response->json('data.user.id');

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.id', $userId);

        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'email' => 'nadia@example.com',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_a_user_can_log_in_and_revoke_their_token(): void
    {
        $user = User::factory()->create([
            'email' => 'creator@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'creator@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertOk()->assertJsonPath('data.user.id', $user->id);
        $token = $response->json('data.token');

        $this->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk();

        auth()->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'creator@example.com',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'creator@example.com',
            'password' => 'incorrect-password',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Email atau kata sandi salah.');
    }

    public function test_a_user_can_update_their_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patchJson('/api/me', [
            'name' => 'Nadia Home',
            'username' => 'nadia_home',
            'bio' => 'Inspirasi rumah dan dekorasi.',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Nadia Home')
            ->assertJsonPath('data.username', 'nadia_home')
            ->assertJsonPath('data.bio', 'Inspirasi rumah dan dekorasi.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nadia Home',
            'username' => 'nadia_home',
        ]);
    }

    public function test_a_user_can_upload_a_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patchJson('/api/me', [
            'name' => 'Creator',
            'username' => 'creator_photo',
            'bio' => '',
            'avatar' => UploadedFile::fake()->image('avatar.png', 300, 300),
        ])->assertOk()
            ->assertJsonPath('data.username', 'creator_photo')
            ->assertJsonStructure(['data' => ['avatar_url']]);

        $user->refresh();
        $this->assertStringContainsString('/storage/avatars/', $response->json('data.avatar_url'));
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_a_user_can_follow_and_unfollow_another_profile(): void
    {
        $follower = User::factory()->create();
        $followed = User::factory()->create();

        $this->actingAs($follower)
            ->postJson("/api/users/{$followed->id}/follow")
            ->assertOk()
            ->assertJsonPath('is_following', true);

        $this->assertDatabaseHas('follows', [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
        ]);

        $this->actingAs($follower)
            ->getJson("/api/users/{$followed->id}")
            ->assertOk()
            ->assertJsonPath('data.user.followers_count', 1)
            ->assertJsonPath('data.is_following', true)
            ->assertJsonMissingPath('data.user.email');

        $this->actingAs($follower)
            ->deleteJson("/api/users/{$followed->id}/follow")
            ->assertOk()
            ->assertJsonPath('is_following', false);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
        ]);

        $this->actingAs($follower)
            ->postJson("/api/users/{$follower->id}/follow")
            ->assertUnprocessable();
    }
}

