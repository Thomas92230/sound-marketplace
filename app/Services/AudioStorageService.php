<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AudioStorageService
{
    public function store(UploadedFile $file, int $userId): array
    {
        $disk = $this->getAudioDisk();
        $filename = $this->generateSecureFilename($file, $userId);
        
        // Upload avec visibilité publique pour le streaming
        $path = $file->storePubliclyAs('tracks', $filename, $disk);
        
        return [
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'disk' => $disk
        ];
    }

    public function delete(string $path): bool
    {
        $disk = $this->getAudioDisk();
        return Storage::disk($disk)->delete($path);
    }

    public function exists(string $path): bool
    {
        $disk = $this->getAudioDisk();
        return Storage::disk($disk)->exists($path);
    }

    public function download(string $path, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $disk = $this->getAudioDisk();
        return Storage::disk($disk)->download($path, $filename);
    }

    private function getAudioDisk(): string
    {
        // En production, utilise S3, en développement utilise public
        return config('app.env') === 'production' ? 's3' : 'public';
    }

    private function generateSecureFilename(UploadedFile $file, int $userId): string
    {
        $extension = $file->getClientOriginalExtension();
        $hash = hash('sha256', $file->getContent());
        $timestamp = now()->timestamp;
        
        return "{$userId}_{$timestamp}_{$hash}.{$extension}";
    }
}