<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TrackController extends Controller
{

    /**
     * Affiche le catalogue (Page d'accueil)
     */
    public function index(Request $request)
    {
        // Pagination pour éviter de charger toutes les pistes
        $tracks = Track::with('artist')->latest()->paginate(20);
        
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
        if (!Storage::disk('public')->exists($track->full_file_key)) {
            abort(404, 'Fichier non trouvé.');
        }

        // Générer le téléchargement
        return Storage::disk('public')->download(
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

        // Validation simple
        $request->validate([
            'title' => 'required|string|max:255',
            'artist_name' => 'required|string|max:255',
            'price_cents' => 'required|integer|min:1',
            'track' => 'required|file|mimes:mp3|max:20000'
        ], [
            'title.required' => 'Le titre est obligatoire.',
            'artist_name.required' => 'Le nom de l\'artiste est obligatoire.',
            'price_cents.required' => 'Le prix est obligatoire.',
            'price_cents.min' => 'Le prix doit être d\'au moins 1 centime.',
            'track.required' => 'Le fichier audio est obligatoire.',
            'track.mimes' => 'Le fichier doit être au format MP3.',
            'track.max' => 'Le fichier ne peut pas dépasser 20 MB.'
        ]);

        try {
            // Upload simple
            $path = $request->file('track')->store('tracks', 'public');
            
            // Enregistrement en base de données
            Track::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'artist_name' => $request->artist_name,
                'price_cents' => $request->price_cents,
                'full_file_key' => $path,
                'preview_url' => Storage::disk('public')->url($path),
            ]);

            return redirect('/')->with('success', 'Morceau ajouté au catalogue !');
            
        } catch (\Exception $e) {
            \Log::error('Erreur upload track: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}
