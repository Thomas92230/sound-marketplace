<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Track;
use App\Models\Purchase;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_purchase_track()
    {
        $user = User::factory()->create();
        $artist = User::factory()->create(['role' => UserRole::Artist]);
        $track = Track::factory()->create(['user_id' => $artist->id, 'price_cents' => 100]);

        $response = $this->actingAs($user)->post("/tracks/{$track->id}/purchase");

        $response->assertRedirect();
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'track_id' => $track->id,
            'status' => 'pending'
        ]);
    }

    public function test_user_cannot_purchase_own_track()
    {
        $artist = User::factory()->create(['role' => UserRole::Artist]);
        $track = Track::factory()->create(['user_id' => $artist->id]);

        $response = $this->actingAs($artist)->post("/tracks/{$track->id}/purchase");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_user_cannot_purchase_same_track_twice()
    {
        $user = User::factory()->create();
        $artist = User::factory()->create(['role' => UserRole::Artist]);
        $track = Track::factory()->create(['user_id' => $artist->id]);
        
        Purchase::factory()->create([
            'user_id' => $user->id,
            'track_id' => $track->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($user)->post("/tracks/{$track->id}/purchase");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_guest_cannot_purchase_track()
    {
        $track = Track::factory()->create();

        $response = $this->post("/tracks/{$track->id}/purchase");

        $response->assertRedirect('/login');
    }
}
