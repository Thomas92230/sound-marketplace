<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isArtist() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|min:1',
            'artist_name' => 'required|string|max:255|min:1',
            'price_cents' => 'required|integer|min:50|max:999999', // Min 0.50€, Max 9999.99€
            'track' => [
                'required',
                'file',
                'mimes:mp3,wav,flac',
                'max:50000', // 50MB max
                function ($attribute, $value, $fail) {
                    // Validation du type MIME réel
                    $mimeType = $value->getMimeType();
                    $allowedMimes = ['audio/mpeg', 'audio/wav', 'audio/x-wav', 'audio/flac'];
                    
                    if (!in_array($mimeType, $allowedMimes)) {
                        $fail('Le fichier doit être un fichier audio valide.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'price_cents.min' => 'Le prix minimum est de 0.50€',
            'price_cents.max' => 'Le prix maximum est de 9999.99€',
            'track.max' => 'Le fichier ne peut pas dépasser 50MB',
        ];
    }
}