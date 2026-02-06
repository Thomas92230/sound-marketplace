<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'artist_name' => fake()->name(),
            'price_cents' => fake()->numberBetween(50, 1000),
            'full_file_key' => 'tracks/' . fake()->uuid() . '.mp3',
            'preview_url' => fake()->url(),
        ];
    }
}
