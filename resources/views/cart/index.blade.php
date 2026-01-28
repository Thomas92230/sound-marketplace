<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🛒 Mon Panier
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($tracks->count() > 0)
                        <div class="space-y-4">
                            @foreach($tracks as $track)
                                <div class="flex items-center justify-between p-4 border rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="font-semibold">{{ $track->title }}</h4>
                                        <p class="text-gray-600 text-sm">{{ $track->artist_name }}</p>
                                    </div>
                                    
                                    <div class="flex items-center gap-4">
                                        <span class="font-bold">{{ number_format($track->price_cents / 100, 2) }} €</span>
                                        
                                        <form action="{{ route('cart.remove', $track) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                ❌ Retirer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="border-t pt-4">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-xl font-bold">Total : {{ number_format($total / 100, 2) }} €</span>
                                    
                                    <div class="flex gap-2">
                                        <form action="{{ route('cart.clear') }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                                                Vider le panier
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('cart.checkout') }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-medium">
                                                💳 Payer maintenant
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 mb-4">Votre panier est vide</p>
                            <a href="{{ route('home') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                Découvrir la musique
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>