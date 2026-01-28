<?php

namespace Database\Seeders;

use App\Models\Track;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        // Créer quelques artistes
        $artist1 = User::create([
            'name' => 'DJ Shadow',
            'email' => 'djshadow@test.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Artist,
            'bio' => 'Producteur de musique électronique'
        ]);

        $artist2 = User::create([
            'name' => 'Luna Park',
            'email' => 'lunapark@test.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Artist,
            'bio' => 'Groupe de rock indépendant'
        ]);

        // Morceaux de test
        $tracks = [
            ['title' => 'Midnight Groove', 'artist' => $artist1, 'price' => 299],
            ['title' => 'Electric Dreams', 'artist' => $artist1, 'price' => 199],
            ['title' => 'Neon Lights', 'artist' => $artist1, 'price' => 249],
            ['title' => 'City Nights', 'artist' => $artist2, 'price' => 349],
            ['title' => 'Summer Vibes', 'artist' => $artist2, 'price' => 199],
            ['title' => 'Ocean Waves', 'artist' => $artist2, 'price' => 279],
        ];

        foreach ($tracks as $track) {
            Track::create([
                'user_id' => $track['artist']->id,
                'title' => $track['title'],
                'artist_name' => $track['artist']->name,
                'price_cents' => $track['price'],
                'full_file_key' => 'demo/' . strtolower(str_replace(' ', '_', $track['title'])) . '.mp3',
                'preview_url' => 'https://www.soundjay.com/misc/sounds/bell-ringing-05.wav'
            ]);
        }
    }
}