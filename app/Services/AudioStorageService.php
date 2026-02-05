<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AudioStorageService
{
    public function store(UploadedFile $file, int $userId): array
    {
        try {
            // Upload vers Cloudinary avec transformation pour audio
            $result = Cloudinary::uploadFile(
                $file->getRealPath(),
                [
                    'folder' => 'music-marketplace/tracks',
                    'public_id' => $userId . '_' . time() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'resource_type' => 'video', // Pour les fichiers audio
                    'format' => 'mp3'
                ]
            );

            return [
                'path' => $result->getPublicId(),
                'url' => $result->getSecurePath(),
                'disk' => 'cloudinary'
            ];
        } catch (\Exception $e) {
            \Log::error('Erreur Cloudinary: ' . $e->getMessage());
            throw new \Exception('Erreur lors de l\'upload vers Cloudinary: ' . $e->getMessage());
        }
    }

    public function delete(string $publicId): bool
    {
        try {
            Cloudinary::destroy($publicId, ['resource_type' => 'video']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getUrl(string $publicId): string
    {
        return Cloudinary::getUrl($publicId, ['resource_type' => 'video']);
    }
}