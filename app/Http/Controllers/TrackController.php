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
        $query = Track::with('artist');
        
        // Recherche par titre ou artiste
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist_name', 'like', "%{$search}%");
            });
        }
        
        // Filtre par prix
        if ($minPrice = $request->get('min_price')) {
            $query->where('price_cents', '>=', $minPrice * 100);
        }
        if ($maxPrice = $request->get('max_price')) {
            $query->where('price_cents', '<=', $maxPrice * 100);
        }
        
        // Tri
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price_cents');
                break;
            case 'price_desc':
                $query->orderByDesc('price_cents');
                break;
            case 'title':
                $query->orderBy('title');
                break;
            default:
                $query->latest();
        }
        
        $tracks = $query->paginate(20)->withQueryString();
        
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
                'preview_url' => url('storage/' . $path),
            ]);

            return redirect('/')->with('success', 'Morceau ajouté au catalogue !');
            
        } catch (\Exception $e) {
            \Log::error('Erreur upload track: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Supprime un morceau
     */
    public function destroy(Track $track)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est propriétaire ou admin
        if ($track->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Non autorisé');
        }

        // Supprimer le fichier
        if ($track->full_file_key && Storage::disk('public')->exists($track->full_file_key)) {
            Storage::disk('public')->delete($track->full_file_key);
        }

        // Supprimer de la base
        $track->delete();

        return back()->with('success', 'Morceau supprimé avec succès.');
    }

    /**
     * Supprime plusieurs morceaux
     */
    public function bulkDestroy(Request $request)
    {
        $user = auth()->user();
        $trackIds = $request->input('track_ids', []);
        
        if (empty($trackIds)) {
            return back()->with('error', 'Aucun morceau sélectionné.');
        }

        $tracks = Track::whereIn('id', $trackIds)
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere(function($q) use ($user) {
                          if ($user->isAdmin()) $q->whereRaw('1=1');
                      });
            })
            ->get();

        foreach ($tracks as $track) {
            if ($track->full_file_key && Storage::disk('public')->exists($track->full_file_key)) {
                Storage::disk('public')->delete($track->full_file_key);
            }
            $track->delete();
        }

        return back()->with('success', count($tracks) . ' morceau(x) supprimé(s).');
    }

    /**
     * Affiche le formulaire de modification
     */
    public function edit(Track $track)
    {
        $user = auth()->user();
        
        if ($track->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Non autorisé');
        }

        return view('tracks.edit', compact('track'));
    }

    /**
     * Met à jour un morceau
     */
    public function update(Request $request, Track $track)
    {
        $user = auth()->user();
        
        if ($track->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Non autorisé');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'artist_name' => 'required|string|max:255',
            'price_cents' => 'required|integer|min:1',
        ]);

        $track->update([
            'title' => $request->title,
            'artist_name' => $request->artist_name,
            'price_cents' => $request->price_cents,
        ]);

        return redirect()->route('dashboard')->with('success', 'Morceau modifié avec succès.');
    }
}
