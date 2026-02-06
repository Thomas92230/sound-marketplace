<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Track;
use App\Models\Purchase;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrackDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_purchased_track()
    {
        Storage::fake('public');
        Storage::disk('public')->put('tracks/test.mp3', 'fake audio content');

        $user = User::factory()->create();
        $artist = User::factory()->create(['role' => UserRole::Artist]);
        $track = Track::factory()->create([
            'user_id' => $artist->id,
            'full_file_key' => 'tracks/test.mp3'
        ]);
        
        Purchase::factory()->create([
            'user_id' => $user->id,
            'track_id' => $track->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($user)->get("/tracks/{$track->id}/download");

        $response->assertStatus(200);
    }

    public function test_user_cannot_download_unpurchased_track()
    {
        $user = User::factory()->create();
        $track = Track::factory()->create();

        $response = $this->actingAs($user)->get("/tracks/{$track->id}/download");

        $response->assertStatus(403);
    }

    public function test_artist_can_download_own_track()
    {
        Storage::fake('public');
        Storage::disk('public')->put('tracks/test.mp3', 'fake audio content');

        $artist = User::factory()->create(['role' => UserRole::Artist]);
        $track = Track::factory()->create([
            'user_id' => $artist->id,
            'full_file_key' => 'tracks/test.mp3'
        ]);

        $response = $this->actingAs($artist)->get("/tracks/{$track->id}/download");

        $response->assertStatus(200);
    }

    public function test_guest_cannot_download_track()
    {
        $track = Track::factory()->create();

        $response = $this->get("/tracks/{$track->id}/download");

        $response->assertStatus(403);
    }
}
