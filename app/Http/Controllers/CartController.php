<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $tracks = Track::whereIn('id', array_keys($cart))->get();
        $total = $tracks->sum('price_cents');
        
        return view('cart.index', compact('tracks', 'total'));
    }

    public function add(Request $request, Track $track)
    {
        $cart = session('cart', []);
        
        // Vérifier que l'utilisateur n'achète pas son propre morceau
        if ($track->user_id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas acheter votre propre morceau.');
        }
        
        // Vérifier si déjà dans le panier
        if (isset($cart[$track->id])) {
            return back()->with('info', 'Ce morceau est déjà dans votre panier.');
        }
        
        $cart[$track->id] = [
            'title' => $track->title,
            'artist_name' => $track->artist_name,
            'price_cents' => $track->price_cents
        ];
        
        session(['cart' => $cart]);
        
        return back()->with('success', 'Morceau ajouté au panier !');
    }

    public function remove(Track $track)
    {
        $cart = session('cart', []);
        unset($cart[$track->id]);
        session(['cart' => $cart]);
        
        return back()->with('success', 'Morceau retiré du panier.');
    }

    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Panier vidé.');
    }
}