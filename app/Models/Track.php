<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Track extends Model
{
    protected $fillable = ['user_id', 'title', 'artist_name', 'price_cents', 'preview_url', 'full_file_key'];

    public function artist()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function isPurchasedBy(int $userId): bool
    {
        return $this->purchases()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Accesseur pour s'assurer que l'URL de prévisualisation est toujours correcte
     */
    public function getPreviewUrlAttribute($value)
    {
        // Si l'URL contient déjà une URL complète (http:// ou https://), on la retourne telle quelle
        if ($value && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Si la valeur est vide, on essaie de générer l'URL depuis le full_file_key
        if (!$value && isset($this->attributes['full_file_key'])) {
            $path = $this->attributes['full_file_key'];
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }
        }

        // Si la valeur ressemble à un chemin relatif, on génère l'URL publique
        if ($value && !str_starts_with($value, 'http')) {
            // Si c'est un chemin relatif (ex: "tracks/xxx.mp3"), on génère l'URL
            if (Storage::disk('public')->exists($value)) {
                return Storage::disk('public')->url($value);
            }
            // Sinon, on assume que c'est un chemin et on génère quand même l'URL
            return Storage::disk('public')->url($value);
        }

        // Retourner la valeur telle quelle (pour S3 ou autres)
        return $value;
    }
}
