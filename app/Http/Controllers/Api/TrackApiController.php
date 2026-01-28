<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;

class TrackApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Track::with('artist');
        
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist_name', 'like', "%{$search}%");
            });
        }
        
        $tracks = $query->latest()->paginate(20);
        
        return response()->json($tracks);
    }

    public function show(Track $track)
    {
        $track->load('artist');
        return response()->json($track);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'artist_name' => 'required|string|max:255',
            'price_cents' => 'required|integer|min:1',
            'track' => 'required|file|mimes:mp3|max:20000'
        ]);

        $path = $request->file('track')->store('tracks', 'public');
        
        $track = Track::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'artist_name' => $request->artist_name,
            'price_cents' => $request->price_cents,
            'full_file_key' => $path,
            'preview_url' => \Storage::disk('public')->url($path),
        ]);

        return response()->json($track, 201);
    }
}