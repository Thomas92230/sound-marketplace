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
                'mimes:mp3,mpeg,mpga',
                'max:50000', // 50MB
                function ($attribute, $value, $fail) {
                    // Validation du MIME type réel
                    $mimeType = $value->getMimeType();
                    $allowedMimes = ['audio/mpeg', 'audio/mp3', 'audio/mpeg3', 'audio/x-mpeg-3'];
                    
                    if (!in_array($mimeType, $allowedMimes)) {
                        $fail('Le fichier doit être un fichier audio MP3 valide.');
                    }
                    
                    // Vérification de la taille minimale (éviter les fichiers vides)
                    if ($value->getSize() < 10000) { // 10KB minimum
                        $fail('Le fichier audio est trop petit. Minimum 10 KB.');
                    }
                    
                    // Vérification de l'extension
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['mp3'])) {
                        $fail('L\'extension du fichier doit être .mp3');
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