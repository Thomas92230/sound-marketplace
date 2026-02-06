<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'track_id' => Track::factory(),
            'amount_cents' => fake()->numberBetween(50, 1000),
            'payment_id' => 'test_' . fake()->uuid(),
            'payment_method' => 'stripe',
            'status' => 'completed',
        ];
    }
}
