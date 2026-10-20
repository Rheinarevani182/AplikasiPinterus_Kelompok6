<?php

namespace Tests\Feature;

use App\Models\Pin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_save_and_remove_a_pin(): void
    {
        $user = User::factory()->create();
        $pin = Pin::query()->create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Inspirasi Ruang',
            'description' => 'Ruang yang nyaman',
            'image_url' => 'https://example.com/room.jpg',
        ]);

        $this->actingAs($user)
            ->postJson("/api/pins/{$pin->id}/save")
            ->assertOk()
            ->assertJsonPath('is_saved', true);

        $this->assertDatabaseHas('pin_user', [
            'user_id' => $user->id,
            'pin_id' => $pin->id,
        ]);
        $this->actingAs($user)
            ->getJson('/api/me/saved-pins')
            ->assertOk()
            ->assertJsonPath('data.0.id', $pin->id)
            ->assertJsonPath('data.0.is_saved', true);

        $this->actingAs($user)
            ->deleteJson("/api/pins/{$pin->id}/save")
            ->assertOk()
            ->assertJsonPath('is_saved', false);

        $this->assertDatabaseMissing('pin_user', [
            'user_id' => $user->id,
            'pin_id' => $pin->id,
        ]);
    }

    public function test_only_the_pin_owner_can_delete_a_pin(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $pin = Pin::query()->create([
            'user_id' => $owner->id,
            'title' => 'Pin Milik Saya',
            'description' => null,
            'image_url' => 'https://example.com/pin.jpg',
        ]);

        $this->actingAs($otherUser)
            ->deleteJson("/api/pins/{$pin->id}")
            ->assertForbidden();

        $this->assertModelExists($pin);

        $this->actingAs($owner)
            ->deleteJson("/api/pins/{$pin->id}")
            ->assertOk();

        $this->assertModelMissing($pin);
    }

    public function test_a_user_can_report_another_users_pin_once(): void
    {
        $reporter = User::factory()->create();
        $owner = User::factory()->create();
        $pin = Pin::query()->create([
            'user_id' => $owner->id,
            'title' => 'Pin Bermasalah',
            'description' => null,
            'image_url' => 'https://example.com/pin.jpg',
        ]);

        $this->actingAs($reporter)
            ->postJson("/api/pins/{$pin->id}/report", [
                'reason' => 'Konten tidak pantas',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Laporan berhasil dikirim.');

        $this->actingAs($reporter)
            ->postJson("/api/pins/{$pin->id}/report", [
                'reason' => 'Konten tidak pantas',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Pin ini sudah pernah kamu laporkan.');

        $this->assertDatabaseCount('pin_reports', 1);
        $this->assertDatabaseHas('pin_reports', [
            'user_id' => $reporter->id,
            'pin_id' => $pin->id,
            'reason' => 'Konten tidak pantas',
        ]);
    }

    public function test_a_user_cannot_report_their_own_pin(): void
    {
        $owner = User::factory()->create();
        $pin = Pin::query()->create([
            'user_id' => $owner->id,
            'title' => 'Pin Milik Saya',
            'description' => null,
            'image_url' => 'https://example.com/pin.jpg',
        ]);

        $this->actingAs($owner)
            ->postJson("/api/pins/{$pin->id}/report", [
                'reason' => 'Alasan laporan',
            ])
            ->assertUnprocessable();

        $this->assertDatabaseCount('pin_reports', 0);
    }
}
