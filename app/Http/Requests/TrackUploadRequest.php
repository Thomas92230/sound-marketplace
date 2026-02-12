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
            'price_cents' => 'required|integer|min:1|max:999999',
            'track' => [
                'required',
                'file',
                'mimes:mp3,mpeg,mpga,wav,ogg,m4a,aac,flac',
                'max:100000', // 100MB
                function ($attribute, $value, $fail) {
                    $mimeType = $value->getMimeType();
                    $allowedMimes = [
                        'audio/mpeg', 'audio/mp3', 'audio/mpeg3', 'audio/x-mpeg-3',
                        'audio/wav', 'audio/x-wav', 'audio/wave',
                        'audio/ogg', 'audio/x-ogg', 'application/ogg',
                        'audio/mp4', 'audio/x-m4a', 'audio/m4a',
                        'audio/aac', 'audio/x-aac',
                        'audio/flac', 'audio/x-flac',
                        'application/octet-stream'
                    ];
                    
                    if (!in_array($mimeType, $allowedMimes)) {
                        $fail('Format audio non supporté: ' . $mimeType);
                    }
                    
                    if ($value->getSize() < 1000) {
                        $fail('Le fichier audio est trop petit.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'title.min' => 'Le titre doit contenir au moins 1 caractère.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'artist_name.required' => 'Le nom de l\'artiste est obligatoire.',
            'artist_name.min' => 'Le nom de l\'artiste doit contenir au moins 1 caractère.',
            'artist_name.max' => 'Le nom de l\'artiste ne peut pas dépasser 255 caractères.',
            'price_cents.required' => 'Le prix est obligatoire.',
            'price_cents.min' => 'Le prix doit être d\'au moins 1 centime.',
            'price_cents.max' => 'Le prix ne peut pas dépasser 9999.99€.',
            'track.required' => 'Le fichier audio est obligatoire.',
            'track.file' => 'Le fichier doit être un fichier valide.',
            'track.mimes' => 'Le fichier doit être au format MP3.',
            'track.max' => 'Le fichier ne peut pas dépasser 50 MB.',
        ];
    }
}