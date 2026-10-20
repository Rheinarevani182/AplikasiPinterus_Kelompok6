<?php

namespace Tests\Feature;

use App\Models\Pin;
use App\Models\PinReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountSettingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_private_profile_hides_its_posts_until_followed(): void
    {
        $owner = User::factory()->create(['private_account' => true]);
        $visitor = User::factory()->create();
        $pin = Pin::query()->create([
            'user_id' => $owner->id,
            'title' => 'Private inspiration',
            'image_url' => 'https://example.test/private.jpg',
        ]);

        $this->actingAs($visitor)
            ->getJson('/api/pins')
            ->assertOk()
            ->assertJsonMissing(['id' => $pin->id]);

        $this->actingAs($visitor)
            ->getJson("/api/users/{$owner->id}")
            ->assertOk()
            ->assertJsonPath('data.is_private', true)
            ->assertJsonPath('data.pins', []);

        $this->actingAs($visitor)
            ->postJson("/api/users/{$owner->id}/follow")
            ->assertOk();

        $this->actingAs($visitor)
            ->getJson("/api/users/{$owner->id}")
            ->assertOk()
            ->assertJsonPath('data.is_private', false)
            ->assertJsonPath('data.pins.0.id', $pin->id);
    }

    public function test_a_user_can_change_profile_visibility(): void
    {
        $user = User::factory()->create(['username' => 'settings_user']);

        $this->actingAs($user)
            ->patchJson('/api/me', [
                'name' => $user->name,
                'username' => $user->username,
                'bio' => $user->bio,
                'private_account' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.private_account', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'private_account' => true,
        ]);
    }

    public function test_a_user_can_update_their_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)
            ->patchJson('/api/me/password', [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Kata sandi berhasil diperbarui.');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_a_user_cannot_update_their_password_with_the_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);
        $originalPasswordHash = $user->password;

        $this->actingAs($user)
            ->patchJson('/api/me/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');

        $this->assertSame($originalPasswordHash, $user->fresh()->password);
    }

    public function test_a_user_can_only_view_their_own_pin_reports(): void
    {
        $reporter = User::factory()->create();
        $otherUser = User::factory()->create();
        $pin = Pin::query()->create([
            'user_id' => $otherUser->id,
            'title' => 'Reported inspiration',
            'image_url' => 'https://example.test/reported.jpg',
        ]);
        PinReport::query()->create([
            'user_id' => $reporter->id,
            'pin_id' => $pin->id,
            'reason' => 'This image is inappropriate.',
        ]);

        $this->actingAs($reporter)
            ->getJson('/api/me/reports')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reason', 'This image is inappropriate.')
            ->assertJsonPath('data.0.pin.id', $pin->id);

        $this->actingAs($otherUser)
            ->getJson('/api/me/reports')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
