<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService
{
    private string $url;
    private string $key;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->key = config('services.supabase.service_key');
    }

    public function insertTrack(array $data): array
    {
        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => "Bearer {$this->key}",
            'Content-Type' => 'application/json',
        ])->post("{$this->url}/rest/v1/tracks", $data);

        return $response->json();
    }

    public function getTracks(array $filters = []): array
    {
        $query = http_build_query($filters);
        
        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => "Bearer {$this->key}",
        ])->get("{$this->url}/rest/v1/tracks?{$query}");

        return $response->json();
    }

    public function uploadFile(string $bucket, string $path, $file): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->key}",
        ])->attach('file', $file, $path)
          ->post("{$this->url}/storage/v1/object/{$bucket}/{$path}");

        return $response->json();
    }
}