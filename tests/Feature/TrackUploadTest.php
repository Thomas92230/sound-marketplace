<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Track;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrackUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_can_upload_track()
    {
        Storage::fake('public');
        $artist = User::factory()->create(['role' => UserRole::Artist]);
        
        $response = $this->actingAs($artist)->post('/upload', [
            'title' => 'Test Track',
            'artist_name' => 'Test Artist',
            'price_cents' => 100,
            'track' => UploadedFile::fake()->create('test.mp3', 1000, 'audio/mpeg')
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('tracks', ['title' => 'Test Track']);
    }

    public function test_regular_user_cannot_upload_track()
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        
        $response = $this->actingAs($user)->post('/upload', [
            'title' => 'Test Track',
            'artist_name' => 'Test Artist',
            'price_cents' => 100,
            'track' => UploadedFile::fake()->create('test.mp3', 1000, 'audio/mpeg')
        ]);

        $response->assertStatus(403);
    }

    public function test_upload_requires_valid_mp3_file()
    {
        $artist = User::factory()->create(['role' => UserRole::Artist]);
        
        $response = $this->actingAs($artist)->post('/upload', [
            'title' => 'Test Track',
            'artist_name' => 'Test Artist',
            'price_cents' => 100,
            'track' => UploadedFile::fake()->create('test.txt', 100)
        ]);

        $response->assertSessionHasErrors('track');
    }

    public function test_upload_requires_minimum_price()
    {
        $artist = User::factory()->create(['role' => UserRole::Artist]);
        
        $response = $this->actingAs($artist)->post('/upload', [
            'title' => 'Test Track',
            'artist_name' => 'Test Artist',
            'price_cents' => 0,
            'track' => UploadedFile::fake()->create('test.mp3', 1000, 'audio/mpeg')
        ]);

        $response->assertSessionHasErrors('price_cents');
    }
}
