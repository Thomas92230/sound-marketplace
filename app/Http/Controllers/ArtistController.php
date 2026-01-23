<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtistController extends Controller
{
    /**
     * Affiche le profil public d'un artiste
     */
    public function show(User $artist): View
    {
        $tracks = Track::where('user_id', $artist->id)
            ->latest()
            ->get();

        return view('artists.show', compact('artist', 'tracks'));
    }

    /**
     * Affiche le formulaire d'édition du profil artiste
     */
    public function edit(Request $request): View
    {
        return view('artists.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Met à jour le profil de l'artiste
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->bio = $request->bio;

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return redirect()->route('artists.edit')->with('success', 'Profil mis à jour avec succès.');
    }
}