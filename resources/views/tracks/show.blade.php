<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $track->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    <!-- Informations du morceau -->
                    <div class="flex items-start gap-6">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $track->title }}</h1>
                            <p class="text-xl text-gray-600 mb-4">{{ $track->artist_name }}</p>
                            
                            @if($track->artist)
                                <a href="{{ route('artists.show', $track->artist) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    Voir le profil de l'artiste →
                                </a>
                            @endif
                        </div>
                        
                        <div class="text-right">
                            <div class="text-3xl font-bold text-gray-900 mb-4">
                                {{ number_format($track->price_cents / 100, 2) }} €
                            </div>
                            
                            @auth
                                @if($isPurchased)
                                    <a href="{{ route('tracks.download', $track) }}" 
                                       class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md font-medium">
                                        Télécharger le fichier complet
                                    </a>
                                @elseif($track->user_id !== auth()->id())
                                    <div class="space-y-2">
                                        <form action="{{ route('purchases.store', $track) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="payment_method" value="stripe">
                                            <button type="submit" 
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium w-full mb-2">
                                                Acheter avec Stripe
                                            </button>
                                        </form>
                                        <form action="{{ route('purchases.store', $track) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="payment_method" value="paypal">
                                            <button type="submit" 
                                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-md font-medium w-full">
                                                Acheter avec PayPal
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-gray-500">Votre morceau</span>
                                @endif
                            @else
                                <a href="{{ route('login') }}" 
                                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium">
                                    Se connecter pour acheter
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Lecteur audio -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Aperçu</h3>
                        <audio controls class="w-full" preload="metadata">
                            <source src="{{ $track->preview_url }}" type="audio/mpeg">
                            <source src="https://www.soundjay.com/misc/sounds/bell-ringing-05.wav" type="audio/wav">
                            Votre navigateur ne supporte pas l'élément audio.
                        </audio>
                        @if(!$isPurchased && auth()->check() && $track->user_id !== auth()->id())
                            <p class="text-sm text-gray-500 mt-2">Aperçu limité - Achetez pour télécharger le fichier complet</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>