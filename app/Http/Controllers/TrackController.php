<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TrackController extends Controller
{
    protected function audioDisk(): string
    {
        // Utilise FILESYSTEM_DISK (ex: "public" en dev, "s3" en prod)
        return (string) config('filesystems.default', 'public');
    }

    /**
     * Affiche le catalogue (Page d'accueil)
     */
    public function index(Request $request)
    {
        // On récupère toutes les pistes triées par les plus récentes
        $tracks = Track::with('artist')->latest()->get();
        
        // Vérifier quels morceaux ont été achetés par l'utilisateur connecté
        $purchasedTrackIds = [];
        if ($request->user()) {
            $purchasedTrackIds = Purchase::where('user_id', $request->user()->id)
                ->where('status', 'completed')
                ->pluck('track_id')
                ->toArray();
        }

        return view('welcome', compact('tracks', 'purchasedTrackIds'));
    }

    /**
     * Affiche les détails d'un morceau
     */
    public function show(Track $track)
    {
        $track->load('artist');
        $isPurchased = false;
        
        if (Auth::check()) {
            $isPurchased = $track->isPurchasedBy(Auth::id());
        }

        return view('tracks.show', compact('track', 'isPurchased'));
    }

    /**
     * Télécharge le fichier complet (uniquement si acheté)
     */
    public function download(Track $track)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Vous devez être connecté pour télécharger.');
        }

        // Vérifier si l'utilisateur a acheté le morceau
        if (!$track->isPurchasedBy($user->id) && $track->user_id !== $user->id) {
            abort(403, 'Vous devez acheter ce morceau pour le télécharger.');
        }

        $disk = $this->audioDisk();

        // Vérifier que le fichier existe
        if (!Storage::disk($disk)->exists($track->full_file_key)) {
            abort(404, 'Fichier non trouvé.');
        }

        // Générer le téléchargement
        return Storage::disk($disk)->download(
            $track->full_file_key,
            "{$track->title} - {$track->artist_name}.mp3"
        );
    }

    /**
     * Affiche le dashboard avec les pistes de l'utilisateur
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        // On récupère les pistes de l'utilisateur connecté
        $tracks = Track::where('user_id', $user->id)
            ->latest()
            ->get();

        return view('dashboard', compact('tracks'));
    }

    /**
     * Affiche le formulaire d'upload
     */
    public function showForm()
    {
        return view('upload');
    }

    /**
     * Gère l'enregistrement du fichier et des données
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // 1. Validation des données
        $request->validate([
            'title' => 'required|string|max:255',
            'artist_name' => 'required|string|max:255',
            'price_cents' => 'required|numeric',
            'track' => 'required|mimes:mp3|max:20000', // Limite à 20 Mo
        ]);

        // 2. Upload (disque configurable: public en dev, s3 en prod)
        if ($request->hasFile('track')) {
            $disk = $this->audioDisk();

            // Sur S3, on stocke en public pour permettre le streaming du preview.
            // Sur "public" (local), storePublicly garde le même comportement attendu.
            $path = $request->file('track')->storePublicly('tracks', $disk);

            // 3. Enregistrement en base de données MySQL
            Track::create([
                'user_id' => $user?->id,
                'title' => $request->title,
                'artist_name' => $request->artist_name,
                'price_cents' => $request->price_cents,
                'full_file_key' => $path,
                'preview_url' => Storage::disk($disk)->url($path),
            ]);

            return redirect('/')->with('success', 'Morceau ajouté au catalogue !');
        }

        return back()->with('error', 'Une erreur est survenue lors de l\'upload.');
    }
}
