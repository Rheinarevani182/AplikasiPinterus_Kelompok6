<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PinApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_it_lists_pins(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/pins', [
            'title' => 'Desk Setup',
            'description' => 'Minimal workspace',
            'image' => UploadedFile::fake()->image('desk.jpg', 640, 480),
        ]);

        $response = $this->actingAs($user)->getJson('/api/pins');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    ['id', 'title', 'description', 'image_url', 'link_url', 'user', 'is_saved'],
                ],
            ])
            ->assertJsonPath('success', true);
    }

    public function test_it_creates_a_pin_with_uploaded_image(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/pins', [
            'title' => 'Cozy Corner',
            'description' => 'Warm reading nook',
            'category' => 'Dekorasi',
            'link_url' => 'https://example.com/cozy-corner',
            'image' => UploadedFile::fake()->image('corner.png', 800, 900),
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Pin berhasil dibuat')
            ->assertJsonPath('data.title', 'Cozy Corner')
            ->assertJsonPath('data.description', 'Warm reading nook')
            ->assertJsonPath('data.category', 'Dekorasi')
            ->assertJsonPath('data.link_url', 'https://example.com/cozy-corner')
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertDatabaseHas('pins', [
            'user_id' => $user->id,
            'title' => 'Cozy Corner',
            'category' => 'Dekorasi',
            'link_url' => 'https://example.com/cozy-corner',
        ]);
    }

    public function test_it_rejects_an_unknown_pin_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/pins', [
            'title' => 'Unknown category',
            'category' => 'Tidak dikenal',
            'image' => UploadedFile::fake()->image('unknown.png'),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('category');

        $this->assertDatabaseCount('pins', 0);
    }

    public function test_creating_a_pin_requires_authentication(): void
    {
        $this->postJson('/api/pins', [
            'title' => 'Cozy Corner',
            'image' => UploadedFile::fake()->image('corner.png'),
        ])->assertUnauthorized();
    }

    public function test_a_pin_rejects_non_http_destination_links(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/pins', [
            'title' => 'Unsafe Link',
            'link_url' => 'javascript:alert(1)',
            'image' => UploadedFile::fake()->image('unsafe.png'),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('link_url');

        $this->assertDatabaseCount('pins', 0);
    }
}

