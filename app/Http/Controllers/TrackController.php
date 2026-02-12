<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\Purchase;
use App\Services\AudioStorageService;
use App\Http\Requests\TrackUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TrackController extends Controller
{
    public function __construct(
        private AudioStorageService $audioStorage
    ) {}

    /**
     * Affiche le catalogue (Page d'accueil)
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 12);
        $perPage = in_array($perPage, [12, 24, 48]) ? $perPage : 12;
        
        $query = Track::query()
            ->select('tracks.*')
            ->with(['artist:id,name,email']);
        
        // Recherche par titre ou artiste
        if ($search = $request->get('search')) {
            $searchTerm = '%' . $search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('artist_name', 'like', $searchTerm);
            });
        }
        
        // Filtre par prix
        if ($minPrice = $request->get('min_price')) {
            $query->where('price_cents', '>=', $minPrice * 100);
        }
        if ($maxPrice = $request->get('max_price')) {
            $query->where('price_cents', '<=', $maxPrice * 100);
        }
        
        // Tri optimisé
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price_cents', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price_cents', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $tracks = $query->paginate($perPage)->withQueryString();
        
        // Optimisation: une seule requête pour les achats
        $purchasedTrackIds = [];
        if ($user = $request->user()) {
            $purchasedTrackIds = Purchase::where('user_id', '=', $user->id)
                ->where('status', '=', 'completed')
                ->pluck('track_id')
                ->toArray();
        }

        return view('welcome', compact('tracks', 'purchasedTrackIds', 'perPage'));
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
        try {
            $user = Auth::user();

            if (!$user) {
                abort(403, 'Vous devez être connecté pour télécharger.');
            }

            // Vérifier si l'utilisateur a acheté le morceau
            if (!$track->isPurchasedBy($user->id) && $track->user_id !== $user->id) {
                Log::warning('Unauthorized download attempt', [
                    'user_id' => $user->id,
                    'track_id' => $track->id
                ]);
                abort(403, 'Vous devez acheter ce morceau pour le télécharger.');
            }

            // Vérifier que le fichier existe
            if (!Storage::disk('public')->exists($track->full_file_key)) {
                Log::error('Track file not found', [
                    'track_id' => $track->id,
                    'file_key' => $track->full_file_key
                ]);
                abort(404, 'Fichier non trouvé.');
            }

            Log::info('Track downloaded', [
                'user_id' => $user->id,
                'track_id' => $track->id
            ]);

            // Générer le téléchargement
            return Storage::disk('public')->download(
                $track->full_file_key,
                "{$track->title} - {$track->artist_name}.mp3"
            );
        } catch (\Exception $e) {
            Log::error('Error downloading track', [
                'error' => $e->getMessage(),
                'track_id' => $track->id
            ]);
            return back()->with('error', 'Erreur lors du téléchargement.');
        }
    }

    /**
     * Affiche le dashboard avec les pistes de l'utilisateur
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        $tracks = Track::where('user_id', '=', $user->id)
            ->select('id', 'title', 'artist_name', 'price_cents', 'created_at')
            ->latest()
            ->paginate(15);

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
    public function store(TrackUploadRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        try {
            $file = $request->file('track');
            
            // Vérifications supplémentaires
            if (!$file->isValid()) {
                throw new \Exception('Le fichier uploadé est invalide.');
            }
            
            $path = $file->store('tracks', 'public');
            
            if (!$path) {
                throw new \Exception('Échec de l\'upload du fichier.');
            }
            
            $track = Track::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'artist_name' => $validated['artist_name'],
                'price_cents' => $validated['price_cents'],
                'full_file_key' => $path,
                'preview_url' => Storage::disk('public')->url($path),
            ]);

            Log::info('Track uploaded', ['track_id' => $track->id, 'size' => $file->getSize()]);
            return redirect('/')->with('success', 'Morceau ajouté au catalogue !');
            
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DB error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Erreur de base de données.'])->withInput();
        } catch (\Exception $e) {
            Log::error('Upload error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Erreur lors de l\'upload.'])->withInput();
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
                $query->where('user_id', '=', $user->id)
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
