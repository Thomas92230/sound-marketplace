<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Track;
use App\Models\Purchase;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    protected function audioDisk(): string
    {
        return (string) config('filesystems.default', 'public');
    }

    /**
     * Affiche le tableau de bord admin
     */
    public function index(): View
    {
        $stats = [
            'users_count' => User::count(),
            'artists_count' => User::where('role', 'artist')->orWhere('role', 'admin')->count(),
            'tracks_count' => Track::count(),
            'purchases_count' => Purchase::where('status', 'completed')->count(),
            'total_revenue' => Purchase::where('status', 'completed')->sum('amount_cents'),
            'pending_payouts' => Payout::where('status', 'pending')->sum('amount_cents'),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Liste tous les utilisateurs
     */
    public function users(Request $request): View
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->validated('search') ?? $request->input('search', '');
            $search = trim($search);
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
        }

        if ($request->has('role') && in_array($request->input('role'), ['user', 'artist', 'admin'])) {
            $query->where('role', '=', $request->input('role'));
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Met à jour le rôle d'un utilisateur
     */
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,artist,admin',
        ]);

        $user->role = $request->role;
        $user->save();

        return back()->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Liste tous les morceaux
     */
    public function tracks(Request $request): View
    {
        $query = Track::with('artist');

        if ($request->has('search')) {
            $search = trim($request->input('search', ''));
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('artist_name', 'like', '%' . $search . '%');
                });
            }
        }

        $tracks = $query->latest()->paginate(20);

        return view('admin.tracks.index', compact('tracks'));
    }

    /**
     * Supprime un morceau
     */
    public function deleteTrack(Track $track)
    {
        $disk = $this->audioDisk();

        // Supprimer le fichier audio s'il existe
        if ($track->full_file_key && Storage::disk($disk)->exists($track->full_file_key)) {
            Storage::disk($disk)->delete($track->full_file_key);
        }

        $track->delete();

        return back()->with('success', 'Morceau supprimé avec succès.');
    }

    /**
     * Liste tous les achats
     */
    public function purchases(Request $request): View
    {
        $query = Purchase::with(['user', 'track']);

        if ($request->has('status') && in_array($request->input('status'), ['pending', 'completed', 'failed'])) {
            $query->where('status', '=', $request->input('status'));
        }

        $purchases = $query->latest()->paginate(20);

        return view('admin.purchases.index', compact('purchases'));
    }

    /**
     * Liste tous les paiements aux artistes
     */
    public function payouts(Request $request): View
    {
        $query = Payout::with(['user', 'purchase']);

        if ($request->has('status') && in_array($request->input('status'), ['pending', 'paid', 'failed'])) {
            $query->where('status', '=', $request->input('status'));
        }

        $payouts = $query->latest()->paginate(20);

        return view('admin.payouts.index', compact('payouts'));
    }

    /**
     * Marque un payout comme payé (mode manuel MVP)
     */
    public function markPayoutPaid(Payout $payout)
    {
        if ($payout->status !== 'paid') {
            $payout->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        return back()->with('success', 'Paiement marqué comme payé.');
    }
}